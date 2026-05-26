<?php
namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Answer;
use App\Services\AttemptScoringService;
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
            ->whereHas('question', fn ($q) => $q->where('type', 'text'))
            ->with(['question'])
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
                'is_correct' => $safeScore > 0,
                'review_score' => $safeScore,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        $percentage = app(AttemptScoringService::class)->recalculateAfterReview($attempt);
        $attempt->update(['score' => $percentage, 'pending_manual_review' => false]);
        return redirect()->route('teacher.reviews.index')->with('success', 'Ответы проверены');
    }
}