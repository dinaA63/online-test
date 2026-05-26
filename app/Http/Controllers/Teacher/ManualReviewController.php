<?php
namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Answer;
use Illuminate\Http\Request;

class ManualReviewController extends Controller {
    public function index() {
        $pendingAttempts = Attempt::whereNotNull('finished_at')
                                  ->where('pending_manual_review', true)
                                  ->whereHas('test', function ($query) {
                                      $query->where('created_by', auth()->id());
                                  })
                                  ->with(['test', 'user'])
                                  ->orderByDesc('finished_at')
                                  ->get();
        return view('teacher.reviews.index', compact('pendingAttempts'));
    }
    public function show(Attempt $attempt) {
        if ($attempt->test->created_by !== auth()->id()) {
            abort(403);
        }

        $essayAnswers = Answer::where('attempt_id', $attempt->id)
                              ->whereHas('question', fn($q) => $q->where('type', 'text'))
                              ->with('question')
                              ->get();
        return view('teacher.reviews.show', compact('attempt', 'essayAnswers'));
    }
    public function review(Request $request, Attempt $attempt) {
        if ($attempt->test->created_by !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'scores' => 'array',
            'scores.*' => 'numeric|min:0'
        ]);

        foreach (($data['scores'] ?? []) as $answerId => $score) {
            $answer = Answer::where('attempt_id', $attempt->id)->find($answerId);
            if (!$answer) {
                continue;
            }

            $maxScore = $answer->question->points ?? 1;
            $safeScore = min((float) $score, (float) $maxScore);
            $answer->update([
                'is_correct' => $safeScore >= $maxScore,
                'review_score' => $safeScore,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        $attempt->load('test.questions.choices', 'answers');
        $totalPoints = 0.0;
        $earned = 0.0;

        foreach ($attempt->test->questions as $question) {
            $questionPoints = (float) ($question->points ?? 1);
            $totalPoints += $questionPoints;

            $answers = $attempt->answers->where('question_id', $question->id);
            if ($answers->isEmpty()) {
                continue;
            }

            if ($question->type === 'single_choice') {
                $correctChoiceId = $question->choices->firstWhere('is_correct', true)?->id;
                if ($correctChoiceId && (int) $answers->first()->choice_id === (int) $correctChoiceId) {
                    $earned += $questionPoints;
                }
            } elseif ($question->type === 'multiple_choice') {
                $correctChoiceIds = $question->choices->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                $selectedChoiceIds = $answers->pluck('choice_id')->sort()->values()->toArray();
                if ($correctChoiceIds === $selectedChoiceIds) {
                    $earned += $questionPoints;
                }
            } elseif ($question->type === 'text') {
                $earned += (float) ($answers->first()->review_score ?? 0);
            }
        }

        $percentage = $totalPoints > 0 ? round(($earned / $totalPoints) * 100, 2) : 0;
        $attempt->update(['score' => $percentage, 'pending_manual_review' => false]);
        return redirect()->route('teacher.reviews.index')->with('success', 'Ответы проверены');
    }
}