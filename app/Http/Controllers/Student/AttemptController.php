<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Attempt;
use App\Models\Answer;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function start(Test $test)
    {
        // Проверка лимита попыток
        $attemptsCount = Attempt::where('user_id', auth()->id())
            ->where('test_id', $test->id)
            ->count();

        if ($attemptsCount >= $test->max_attempts) {
            return redirect()->route('student.tests.index')
                ->with('error', 'Вы исчерпали лимит попыток для этого теста.');
        }

        // Проверка незавершённой попытки
        $existingAttempt = Attempt::where('user_id', auth()->id())
            ->where('test_id', $test->id)
            ->whereNull('finished_at')
            ->first();

        if ($existingAttempt) {
            // Проверка времени
            if ($test->time_limit > 0) {
                $elapsedMinutes = now()->diffInMinutes($existingAttempt->started_at);
                if ($elapsedMinutes >= $test->time_limit) {
                    $existingAttempt->update(['finished_at' => now(), 'score' => 0]);
                    return redirect()->route('student.tests.index')
                        ->with('error', 'Время теста истекло.');
                }
            }
            return redirect()->route('student.attempt.show', $existingAttempt);
        }

        $attempt = Attempt::create([
            'user_id'    => auth()->id(),
            'test_id'    => $test->id,
            'started_at' => now(),
        ]);

        return redirect()->route('student.attempt.show', $attempt);
    }

    public function show(Attempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $test = $attempt->test;

        // Если тест завершён — показываем результаты
        if ($attempt->finished_at) {
            $questions = $test->questions()->with(['choices', 'matchingPairs', 'sequenceItems'])->get();
            $answers = $attempt->answers()->with('choice')->get()->keyBy('question_id');
            return view('student.attempt.result', compact('attempt', 'test', 'questions', 'answers'));
        }

        // Проверка времени
        if ($test->time_limit > 0) {
            $elapsedMinutes = now()->diffInMinutes($attempt->started_at);
            if ($elapsedMinutes >= $test->time_limit) {
                $evaluation = $this->calculateScore($attempt);
                $attempt->update([
                    'finished_at' => now(),
                    'score' => $evaluation['score'],
                    'pending_manual_review' => $evaluation['pending_manual_review'],
                ]);
                return redirect()->route('student.attempt.show', $attempt)
                    ->with('warning', 'Время теста истекло.');
            }
            $remainingSeconds = ($test->time_limit * 60) - now()->diffInSeconds($attempt->started_at);
        } else {
            $remainingSeconds = null;
        }

        $questions = $test->questions()->with(['choices', 'matchingPairs', 'sequenceItems'])->get();
        $allSavedAnswers = $attempt->answers()->get();
        $savedAnswers = $allSavedAnswers->keyBy('question_id');

        return view('student.attempt.show', compact(
            'attempt', 'test', 'questions', 'remainingSeconds', 'savedAnswers', 'allSavedAnswers'
        ));
    }

    public function submit(Attempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Нет доступа'], 403);
        }

        if ($attempt->finished_at) {
            return response()->json(['success' => false, 'message' => 'Тест уже завершён']);
        }

        $evaluation = $this->calculateScore($attempt);

        $attempt->update([
            'finished_at' => now(),
            'score' => $evaluation['score'],
            'pending_manual_review' => $evaluation['pending_manual_review'],
        ]);

        return response()->json([
            'success' => true,
            'pending_manual_review' => $evaluation['pending_manual_review'],
        ]);
    }

    public function saveAnswer(Request $request, Attempt $attempt)
    {
        if ($attempt->user_id !== auth()->id() || $attempt->finished_at) {
            return response()->json(['error' => 'Недопустимая операция'], 403);
        }

        $data = $request->validate([
            'question_id'   => 'required|exists:questions,id',
            'choice_id'     => 'nullable|exists:choices,id',
            'choice_ids'    => 'nullable|array',
            'choice_ids.*'  => 'exists:choices,id',
            'answer_text'   => 'nullable|string',
            'matching'      => 'nullable|array',
            'matching.*'    => 'nullable|string',
            'sequence_order'=> 'nullable|array',
            'sequence_order.*' => 'integer|exists:sequence_items,id',
        ]);

        $question = \App\Models\Question::findOrFail($data['question_id']);

        // Удаляем старые ответы на этот вопрос
        Answer::where('attempt_id', $attempt->id)
              ->where('question_id', $data['question_id'])
              ->delete();

        if ($question->type === 'single_choice' && isset($data['choice_id'])) {
            Answer::create([
                'attempt_id'  => $attempt->id,
                'question_id' => $data['question_id'],
                'choice_id'   => $data['choice_id'],
                'answer_text' => null,
            ]);
        } elseif ($question->type === 'multiple_choice' && isset($data['choice_ids'])) {
            foreach ($data['choice_ids'] as $choiceId) {
                Answer::create([
                    'attempt_id'  => $attempt->id,
                    'question_id' => $data['question_id'],
                    'choice_id'   => $choiceId,
                    'answer_text' => null,
                ]);
            }
        } elseif ($question->type === 'text' && array_key_exists('answer_text', $data)) {
            Answer::create([
                'attempt_id'  => $attempt->id,
                'question_id' => $data['question_id'],
                'answer_text' => trim((string) $data['answer_text']),
                'choice_id'   => null,
            ]);
        } elseif ($question->type === 'matching' && isset($data['matching'])) {
            Answer::create([
                'attempt_id'  => $attempt->id,
                'question_id' => $data['question_id'],
                'answer_text' => json_encode($data['matching'], JSON_UNESCAPED_UNICODE),
                'choice_id'   => null,
            ]);
        } elseif ($question->type === 'sequence' && isset($data['sequence_order'])) {
            Answer::create([
                'attempt_id'  => $attempt->id,
                'question_id' => $data['question_id'],
                'answer_text' => json_encode(['order' => array_values($data['sequence_order'])], JSON_UNESCAPED_UNICODE),
                'choice_id'   => null,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function history()
    {
        $attempts = Attempt::where('user_id', auth()->id())
                         ->whereNotNull('finished_at')
                         ->with('test')
                         ->orderBy('finished_at', 'desc')
                         ->get();
        return view('student.results', compact('attempts'));
    }

    private function calculateScore(Attempt $attempt): array
    {
        $totalPoints = 0;
        $earnedPoints = 0;
        $pendingManualReview = false;

        foreach ($attempt->test->questions as $question) {
            $userAnswers = $attempt->answers()->where('question_id', $question->id)->get();
            $questionPoints = $question->points ?? 1;

            if ($userAnswers->isEmpty()) {
                $totalPoints += $questionPoints;
                continue;
            }

            $isCorrect = false;

            if ($question->type === 'single_choice') {
                $correctChoice = $question->choices()->where('is_correct', true)->first();
                $selectedChoiceId = $userAnswers->first()->choice_id;
                if ($correctChoice && $selectedChoiceId == $correctChoice->id) {
                    $isCorrect = true;
                }
            } elseif ($question->type === 'multiple_choice') {
                $correctChoiceIds = $question->choices()->where('is_correct', true)->pluck('id')->toArray();
                $userChoiceIds = $userAnswers->pluck('choice_id')->toArray();
                sort($correctChoiceIds);
                sort($userChoiceIds);
                if ($correctChoiceIds === $userChoiceIds) {
                    $isCorrect = true;
                }
            } elseif ($question->type === 'text') {
                $pendingManualReview = true;
                $isCorrect = false;
            } elseif ($question->type === 'matching') {
                $isCorrect = $this->isMatchingAnswerCorrect($question, $userAnswers->first()?->answer_text);
            } elseif ($question->type === 'sequence') {
                $isCorrect = $this->isSequenceAnswerCorrect($question, $userAnswers->first()?->answer_text);
            }

            if ($isCorrect) {
                $earnedPoints += $questionPoints;
            }
            $totalPoints += $questionPoints;
        }

        return [
            'score' => $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0,
            'pending_manual_review' => $pendingManualReview,
        ];
    }

    private function isMatchingAnswerCorrect($question, ?string $answerJson): bool
    {
        if (!$answerJson) {
            return false;
        }

        $userMap = json_decode($answerJson, true);
        if (!is_array($userMap) || empty($userMap)) {
            return false;
        }

        $pairs = $question->matchingPairs;
        if ($pairs->isEmpty()) {
            return false;
        }

        foreach ($pairs as $pair) {
            $selected = $userMap[(string) $pair->id] ?? $userMap[$pair->id] ?? null;
            if ($selected === null) {
                return false;
            }

            if (mb_strtolower(trim($selected)) !== mb_strtolower(trim($pair->right_text))) {
                return false;
            }
        }

        return true;
    }

    private function isSequenceAnswerCorrect($question, ?string $answerJson): bool
    {
        if (!$answerJson) {
            return false;
        }

        $payload = json_decode($answerJson, true);
        $userOrder = $payload['order'] ?? null;
        if (!is_array($userOrder) || empty($userOrder)) {
            return false;
        }

        $question->loadMissing('sequenceItems');
        $correctOrder = $question->sequenceItems
            ->sortBy('correct_order')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $userOrder = array_map('intval', $userOrder);

        return $correctOrder === $userOrder;
    }
}