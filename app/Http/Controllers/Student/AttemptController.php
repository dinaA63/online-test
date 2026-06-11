<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Attempt;
use App\Models\Answer;
use App\Services\AttemptScoringService;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function start(Test $test)
    {
        if (!$test->isAccessibleBy(auth()->user())) {
            return redirect()->route('student.tests.index')
                ->with('error', 'Этот тест недоступен для вашей группы.');
        }

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
                    $evaluation = app(AttemptScoringService::class)->evaluate($existingAttempt);
                    $existingAttempt->update([
                        'finished_at' => now(),
                        'score' => $evaluation['score'],
                        'pending_manual_review' => $evaluation['pending_manual_review'],
                    ]);
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
            $answers = $attempt->answers()->with('choice')->get()->groupBy('question_id');
            $scoring = app(AttemptScoringService::class);
            return view('student.attempt.result', compact('attempt', 'test', 'questions', 'answers', 'scoring'));
        }

        // Проверка времени
        if ($test->time_limit > 0) {
            $elapsedMinutes = now()->diffInMinutes($attempt->started_at);
            if ($elapsedMinutes >= $test->time_limit) {
                $evaluation = app(AttemptScoringService::class)->evaluate($attempt);
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

        $evaluation = app(AttemptScoringService::class)->evaluate($attempt);

        $attempt->update([
            'finished_at' => now(),
            'score' => $evaluation['score'],
            'pending_manual_review' => $evaluation['pending_manual_review'],
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('student.attempt.show', $attempt),
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

}