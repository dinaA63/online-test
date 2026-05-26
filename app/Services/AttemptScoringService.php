<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Question;

class AttemptScoringService
{
    public function evaluate(Attempt $attempt): array
    {
        $attempt->loadMissing([
            'test.questions.choices',
            'test.questions.matchingPairs',
            'test.questions.sequenceItems',
            'answers',
        ]);

        $answersByQuestion = $attempt->answers->groupBy('question_id');
        $totalPoints = 0;
        $earnedPoints = 0;
        $pendingManualReview = false;

        foreach ($attempt->test->questions as $question) {
            $questionPoints = (float) ($question->points ?? 1);
            $totalPoints += $questionPoints;

            $userAnswers = $answersByQuestion->get($question->id, collect());
            if ($userAnswers->isEmpty()) {
                continue;
            }

            $isCorrect = match ($question->type) {
                'single_choice' => $this->isSingleChoiceCorrect($question, $userAnswers),
                'multiple_choice' => $this->isMultipleChoiceCorrect($question, $userAnswers),
                'text' => false,
                'matching' => $this->isMatchingCorrect($question, $userAnswers->first()?->answer_text),
                'sequence' => $this->isSequenceCorrect($question, $userAnswers->first()?->answer_text),
                default => false,
            };

            if ($question->type === 'text') {
                $pendingManualReview = true;
            } elseif ($isCorrect) {
                $earnedPoints += $questionPoints;
            }
        }

        return [
            'score' => $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0,
            'pending_manual_review' => $pendingManualReview,
        ];
    }

    public function recalculateAfterReview(Attempt $attempt): float
    {
        $attempt->loadMissing([
            'test.questions.choices',
            'test.questions.matchingPairs',
            'test.questions.sequenceItems',
            'answers',
        ]);

        $answersByQuestion = $attempt->answers->groupBy('question_id');
        $totalPoints = 0.0;
        $earned = 0.0;

        foreach ($attempt->test->questions as $question) {
            $questionPoints = (float) ($question->points ?? 1);
            $totalPoints += $questionPoints;
            $answers = $answersByQuestion->get($question->id, collect());

            if ($question->type === 'text') {
                $answer = $answers->first();
                $earned += (float) ($answer?->review_score ?? 0);
                continue;
            }

            if ($answers->isEmpty()) {
                continue;
            }

            $isCorrect = match ($question->type) {
                'single_choice' => $this->isSingleChoiceCorrect($question, $answers),
                'multiple_choice' => $this->isMultipleChoiceCorrect($question, $answers),
                'matching' => $this->isMatchingCorrect($question, $answers->first()?->answer_text),
                'sequence' => $this->isSequenceCorrect($question, $answers->first()?->answer_text),
                default => false,
            };

            if ($isCorrect) {
                $earned += $questionPoints;
            }
        }

        return $totalPoints > 0 ? round(($earned / $totalPoints) * 100, 2) : 0;
    }

    private function isSingleChoiceCorrect(Question $question, $userAnswers): bool
    {
        $correctId = $question->choices->firstWhere('is_correct', true)?->id;

        return $correctId && (int) $userAnswers->first()->choice_id === (int) $correctId;
    }

    private function isMultipleChoiceCorrect(Question $question, $userAnswers): bool
    {
        $correctIds = $question->choices->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
        $userIds = $userAnswers->pluck('choice_id')->sort()->values()->toArray();

        return $correctIds === $userIds;
    }

    private function isMatchingCorrect(Question $question, ?string $answerJson): bool
    {
        $userMap = json_decode($answerJson ?? '', true);
        if (!is_array($userMap) || empty($userMap)) {
            return false;
        }

        $pairs = $question->matchingPairs;
        if ($pairs->isEmpty()) {
            return false;
        }

        foreach ($pairs as $pair) {
            $selected = $userMap[(string) $pair->id] ?? $userMap[$pair->id] ?? null;
            if ($selected === null || $selected === '') {
                return false;
            }

            if (is_numeric($selected)) {
                if ((int) $selected !== (int) $pair->id) {
                    return false;
                }
                continue;
            }

            if ($this->normalize($selected) !== $this->normalize($pair->right_text)) {
                return false;
            }
        }

        return true;
    }

    private function isSequenceCorrect(Question $question, ?string $answerJson): bool
    {
        $payload = json_decode($answerJson ?? '', true);
        $userOrder = $payload['order'] ?? null;
        if (!is_array($userOrder) || count($userOrder) === 0) {
            return false;
        }

        $correctOrder = $question->sequenceItems
            ->sortBy('correct_order')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        return array_map('intval', $userOrder) === $correctOrder;
    }

    public function isQuestionCorrect(Question $question, $userAnswers): bool
    {
        $answers = $userAnswers instanceof \Illuminate\Support\Collection
            ? $userAnswers
            : collect($userAnswers ? [$userAnswers] : []);

        if ($answers->isEmpty()) {
            return false;
        }

        return match ($question->type) {
            'single_choice' => $this->isSingleChoiceCorrect($question, $answers),
            'multiple_choice' => $this->isMultipleChoiceCorrect($question, $answers),
            'text' => $this->isTextCorrect($question, $answers->first()),
            'matching' => $this->isMatchingCorrect($question, $answers->first()?->answer_text),
            'sequence' => $this->isSequenceCorrect($question, $answers->first()?->answer_text),
            default => false,
        };
    }

    public function pointsEarned(Question $question, $userAnswers): float
    {
        $max = (float) ($question->points ?? 1);
        $answers = $userAnswers instanceof \Illuminate\Support\Collection
            ? $userAnswers
            : collect($userAnswers ? [$userAnswers] : []);

        if ($question->type === 'text') {
            $answer = $answers->first();
            if ($answer?->reviewed_at !== null) {
                return min((float) ($answer->review_score ?? 0), $max);
            }

            return 0;
        }

        return $this->isQuestionCorrect($question, $answers) ? $max : 0;
    }

    private function isTextCorrect(Question $question, ?\App\Models\Answer $answer): bool
    {
        if (!$answer || empty($question->correct_text)) {
            return false;
        }

        return $this->normalize($answer->answer_text ?? '') === $this->normalize($question->correct_text);
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
    }
}
