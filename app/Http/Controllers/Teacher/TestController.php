<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\Request;
use App\Models\Attempt;
use Illuminate\Support\Facades\Response;
use League\Csv\Writer;

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::where('created_by', auth()->id())->get();
        return view('teacher.tests.index', compact('tests'));
    }

    public function create()
    {
        return view('teacher.tests.create');
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required',
        'description' => 'nullable',
        'time_limit' => 'integer|min:0',
        'max_attempts' => 'integer|min:1',
    ]);

    auth()->user()->tests()->create($validated);

    return redirect()->route('teacher.tests.index')->with('success', 'Тест создан');
}

    public function show(Test $test)
    {
        return view('teacher.tests.show', compact('test'));
    }

    public function edit(Test $test)
    {
        return view('teacher.tests.edit', compact('test'));
    }

    public function update(Request $request, Test $test)
    {
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'time_limit' => 'integer|min:0',
        ]);
        $test->update($validated);
        return redirect()->route('teacher.tests.show', $test)->with('success', 'Тест обновлён');
    }

    public function destroy(Test $test)
    {
        $test->delete();
        return redirect()->route('teacher.tests.index')->with('success', 'Тест удалён');
    }
    public function statistics(Test $test)
{
    $attempts = Attempt::where('test_id', $test->id)
                       ->whereNotNull('finished_at')
                       ->with('user')
                       ->get();
    $totalAttempts = $attempts->count();
    $averageScore = $attempts->avg('score');
    $scores = $attempts->pluck('score');
    $studentResults = $attempts->groupBy('user.name')->map(function($group) {
        return $group->avg('score');
    });
    return view('teacher.tests.statistics', compact('test', 'totalAttempts', 'averageScore', 'studentResults', 'scores'));
}

public function export(Test $test)
{
    $attempts = Attempt::where('test_id', $test->id)
                       ->whereNotNull('finished_at')
                       ->with('user')
                       ->get();
    $csv = Writer::createFromString('');
    $csv->insertOne(['Студент', 'Результат (%)', 'Дата']);
    foreach ($attempts as $attempt) {
        $csv->insertOne([
            $attempt->user->name,
            round($attempt->score, 2),
            $attempt->finished_at->format('d.m.Y H:i')
        ]);
    }
    return Response::make($csv->toString(), 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"stats_{$test->id}.csv\"",
    ]);
}
}