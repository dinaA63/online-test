<?php
namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Answer;
use Illuminate\Http\Request;

class ManualReviewController extends Controller {
    public function index() {
        $pendingAttempts = Attempt::whereNotNull('finished_at')
                                  ->whereNull('score')    // не полностью проверены
                                  ->where('pending_manual_review', true)
                                  ->with(['test', 'user'])
                                  ->get();
        return view('teacher.reviews.index', compact('pendingAttempts'));
    }
    public function show(Attempt $attempt) {
        $essayAnswers = Answer::where('attempt_id', $attempt->id)
                              ->whereHas('question', fn($q) => $q->where('type', 'essay'))
                              ->with('question')
                              ->get();
        return view('teacher.reviews.show', compact('attempt', 'essayAnswers'));
    }
    public function review(Request $request, Attempt $attempt) {
        $data = $request->validate([
            'scores' => 'array',
            'scores.*' => 'integer|min:0'
        ]);
        foreach ($data['scores'] as $answerId => $score) {
            $answer = Answer::find($answerId);
            $answer->update([
                'is_correct' => $score > 0,
                'review_score' => $score,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }
        // пересчитать общий балл попытки
        $totalPoints = $attempt->test->questions->sum('points');
        $earned = Answer::where('attempt_id', $attempt->id)
                        ->where(function($q) {
                            $q->where('is_correct', true)->orWhere('review_score', '>', 0);
                        })->sum('review_score') ?? 0;
        $percentage = $totalPoints ? round($earned / $totalPoints * 100, 2) : 0;
        $attempt->update(['score' => $percentage, 'pending_manual_review' => false]);
        return redirect()->route('teacher.reviews.index')->with('success', 'Ответы проверены');
    }
}