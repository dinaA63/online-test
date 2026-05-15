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
        $attempt = Attempt::create([
            'user_id' => auth()->id(),
            'test_id' => $test->id,
            'started_at' => now(),
        ]);
        return redirect()->route('student.attempt.show', $attempt);
    }

    public function show(Attempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        if ($attempt->finished_at) return redirect()->route('student.results');
        $test = $attempt->test;
        $questions = $test->questions()->with('choices')->get();
        return view('student.attempt.show', compact('attempt', 'test', 'questions'));
    }

    public function submit(Attempt $attempt)
    {
        if ($attempt->finished_at) {
            return response()->json(['success' => false, 'message' => 'Тест уже завершён']);
        }

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($attempt->test->questions as $question) {
            $userAnswers = $attempt->answers()->where('question_id', $question->id)->get();
            if ($userAnswers->isEmpty()) continue;

            $isCorrect = false;
            $questionPoints = $question->points ?? 1;

            if ($question->type == 'single_choice') {
                $correctChoice = $question->choices()->where('is_correct', true)->first();
                $selectedChoiceId = $userAnswers->first()->choice_id;
                if ($correctChoice && $selectedChoiceId == $correctChoice->id) $isCorrect = true;
            } elseif ($question->type == 'multiple_choice') {
                $correctChoiceIds = $question->choices()->where('is_correct', true)->pluck('id')->toArray();
                $userChoiceIds = $userAnswers->pluck('choice_id')->toArray();
                if (empty(array_diff($correctChoiceIds, $userChoiceIds)) && empty(array_diff($userChoiceIds, $correctChoiceIds))) $isCorrect = true;
            } elseif ($question->type == 'text') {
                $correctText = $question->correct_text ?? '';
                $userText = $userAnswers->first()->answer_text ?? '';
                if (strtolower(trim($userText)) == strtolower(trim($correctText))) $isCorrect = true;
            }

            if ($isCorrect) $earnedPoints += $questionPoints;
            $totalPoints += $questionPoints;
        }

        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $attempt->update(['finished_at' => now(), 'score' => round($percentage, 2)]);
        return response()->json(['success' => true]);
    }

    public function saveAnswer(Request $request, Attempt $attempt)
    {
        if ($attempt->user_id !== auth()->id() || $attempt->finished_at) {
            return response()->json(['error' => 'Недопустимая операция'], 403);
        }

        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'choice_id' => 'nullable|exists:choices,id',
            'choice_ids' => 'nullable|array',
            'choice_ids.*' => 'exists:choices,id',
            'answer_text' => 'nullable|string',
        ]);

        $question = \App\Models\Question::findOrFail($data['question_id']);

        if ($question->type == 'single_choice' && isset($data['choice_id'])) {
            Answer::where('attempt_id', $attempt->id)->where('question_id', $data['question_id'])->delete();
            Answer::create(['attempt_id' => $attempt->id, 'question_id' => $data['question_id'], 'choice_id' => $data['choice_id']]);
        } elseif ($question->type == 'multiple_choice' && isset($data['choice_ids'])) {
            Answer::where('attempt_id', $attempt->id)->where('question_id', $data['question_id'])->delete();
            foreach ($data['choice_ids'] as $choiceId) {
                Answer::create(['attempt_id' => $attempt->id, 'question_id' => $data['question_id'], 'choice_id' => $choiceId]);
            }
        } elseif ($question->type == 'text' && isset($data['answer_text'])) {
            Answer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $data['question_id']],
                ['answer_text' => $data['answer_text'], 'choice_id' => null]
            );
        }
        return response()->json(['success' => true]);
    }

    public function history()
    {
        $attempts = Attempt::where('user_id', auth()->id())->whereNotNull('finished_at')->with('test')->orderBy('finished_at', 'desc')->get();
        return view('student.results', compact('attempts'));
    }
}