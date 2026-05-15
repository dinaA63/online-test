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
        return view('student.tests.index', compact('tests', 'completedTests'));
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