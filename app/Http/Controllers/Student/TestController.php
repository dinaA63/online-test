<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Test;

class TestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tests = Test::visibleToStudent($user)
            ->with('group')
            ->withCount('questions')
            ->orderBy('title')
            ->get();

        $completedTests = Attempt::where('user_id', $user->id)
            ->whereNotNull('finished_at')
            ->pluck('test_id');

        $attemptsCount = Attempt::where('user_id', $user->id)
            ->selectRaw('test_id, count(*) as total')
            ->groupBy('test_id')
            ->pluck('total', 'test_id');

        $pendingManualReviewTests = Attempt::where('user_id', $user->id)
            ->where('pending_manual_review', true)
            ->whereNotNull('finished_at')
            ->pluck('test_id');

        return view('student.tests.index', compact('tests', 'completedTests', 'attemptsCount', 'pendingManualReviewTests'));
    }

    public function show(Test $test)
    {
        if (!$test->isAccessibleBy(auth()->user())) {
            abort(403, 'Этот тест недоступен для вашей группы.');
        }

        $attempt = Attempt::where('user_id', auth()->id())
            ->where('test_id', $test->id)
            ->whereNull('finished_at')
            ->first();

        if ($attempt) {
            return redirect()->route('student.attempt.show', $attempt);
        }

        $test->load('group');

        return view('student.tests.show', compact('test'));
    }
}
