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
            $questions = $test->questions()->with('choices')->get();
            $answers = $attempt->answers()->with('choice')->get()->keyBy('question_id');
            return view('student.attempt.result', compact('attempt', 'test', 'questions', 'answers'));
        }

        // Проверка времени
        if ($test->time_limit > 0) {
            $elapsedMinutes = now()->diffInMinutes($attempt->started_at);
            if ($elapsedMinutes >= $test->time_limit) {
                $attempt->update(['finished_at' => now(), 'score' => $this->calculateScore($attempt)]);
                return redirect()->route('student.attempt.show', $attempt)
                    ->with('warning', 'Время теста истекло.');
            }
            $remainingSeconds = ($test->time_limit * 60) - now()->diffInSeconds($attempt->started_at);
        } else {
            $remainingSeconds = null;
        }

        $questions = $test->questions()->with('choices')->get();
        $savedAnswers = $attempt->answers()->get()->keyBy('question_id');

        return view('student.attempt.show', compact(
            'attempt', 'test', 'questions', 'remainingSeconds', 'savedAnswers'
        ));
    }

    public function submit(Attempt $attempt)
    {
        if ($attempt->finished_at) {
            return response()->json(['success' => false, 'message' => 'Тест уже завершён']);
        }

        $score = $this->calculateScore($attempt);

        $attempt->update([
            'finished_at' => now(),
            'score'       => $score,
        ]);

        return response()->json(['success' => true]);
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
        } elseif (in_array($question->type, ['text', 'essay']) && isset($data['answer_text'])) {
            Answer::create([
                'attempt_id'  => $attempt->id,
                'question_id' => $data['question_id'],
                'answer_text' => $data['answer_text'],
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

    private function calculateScore(Attempt $attempt): float
    {
        $totalPoints = 0;
        $earnedPoints = 0;

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
            } elseif (in_array($question->type, ['text', 'essay'])) {
                $correctText = $question->correct_text ?? '';
                $userText = $userAnswers->first()->answer_text ?? '';
                if (!empty($correctText) && strtolower(trim($userText)) === strtolower(trim($correctText))) {
                    $isCorrect = true;
                } else {
                    // Для ручной проверки
                    $isCorrect = false;
                }
            }

            if ($isCorrect) {
                $earnedPoints += $questionPoints;
            }
            $totalPoints += $questionPoints;
        }

        return $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    }
}