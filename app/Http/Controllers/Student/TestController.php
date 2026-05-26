<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Attempt;

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::all();
        $completedTests = Attempt::where('user_id', auth()->id())
                                 ->whereNotNull('finished_at')
                                 ->pluck('test_id');
        $attemptsCount = Attempt::where('user_id', auth()->id())
            ->selectRaw('test_id, count(*) as total')
            ->groupBy('test_id')
            ->pluck('total', 'test_id');

        $pendingManualReviewTests = Attempt::where('user_id', auth()->id())
            ->where('pending_manual_review', true)
            ->whereNotNull('finished_at')
            ->pluck('test_id');

        return view('student.tests.index', compact('tests', 'completedTests', 'attemptsCount', 'pendingManualReviewTests'));
    }

    public function show(Test $test)
    {
        // Если есть незавершённая попытка, перенаправить на неё
        $attempt = Attempt::where('user_id', auth()->id())
                         ->where('test_id', $test->id)
                         ->whereNull('finished_at')
                         ->first();
        if ($attempt) {
            return redirect()->route('student.attempt.show', $attempt);
        }
        return view('student.tests.show', compact('test'));
    }
}