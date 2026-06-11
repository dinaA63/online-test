<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Group;
use App\Models\Test;
use App\Services\TestResultsExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::where('created_by', auth()->id())
            ->with('group')
            ->withCount('questions')
            ->withCount([
                'attempts as pending_reviews_count' => function ($query) {
                    $query->where('pending_manual_review', true)->whereNotNull('finished_at');
                },
            ])
            ->get();

        return view('teacher.tests.index', compact('tests'));
    }

    public function create()
    {
        $groups = Group::orderBy('name')->get();

        return view('teacher.tests.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'integer|min:0',
            'max_attempts' => 'integer|min:1',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $test = auth()->user()->tests()->create($validated);

        return redirect()->route('teacher.tests.show', $test)->with('success', 'Тест создан');
    }

    public function show(Test $test)
    {
        $this->authorizeTest($test);
        $test->load(['group', 'questions.choices', 'questions.matchingPairs', 'questions.sequenceItems']);

        return view('teacher.tests.show', compact('test'));
    }

    public function edit(Test $test)
    {
        $this->authorizeTest($test);
        $groups = Group::orderBy('name')->get();

        return view('teacher.tests.edit', compact('test', 'groups'));
    }

    public function update(Request $request, Test $test)
    {
        $this->authorizeTest($test);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'integer|min:0',
            'max_attempts' => 'integer|min:1',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $test->update($validated);

        return redirect()->route('teacher.tests.show', $test)->with('success', 'Тест обновлён');
    }

    public function destroy(Test $test)
    {
        $this->authorizeTest($test);
        $test->delete();

        return redirect()->route('teacher.tests.index')->with('success', 'Тест удалён');
    }

    public function statistics(Test $test)
    {
        $this->authorizeTest($test);

        $attempts = Attempt::where('test_id', $test->id)
            ->whereNotNull('finished_at')
            ->with('user')
            ->get();
        $totalAttempts = $attempts->count();
        $averageScore = $attempts->avg('score');
        $scores = $attempts->pluck('score');
        $studentResults = $attempts->groupBy('user.name')->map(fn ($group) => $group->avg('score'));

        return view('teacher.tests.statistics', compact('test', 'totalAttempts', 'averageScore', 'studentResults', 'scores'));
    }

    public function exportCsv(Test $test)
    {
        $this->authorizeTest($test);
        $filename = 'results_' . $this->safeFilename($test->title) . '.csv';

        return Response::make(app(TestResultsExportService::class)->toCsv($test), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportDetailedCsv(Test $test)
    {
        $this->authorizeTest($test);
        $filename = 'results_detailed_' . $this->safeFilename($test->title) . '.csv';

        return Response::make(app(TestResultsExportService::class)->toDetailedCsv($test), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportExcel(Test $test)
    {
        $this->authorizeTest($test);
        $filename = 'results_' . $this->safeFilename($test->title) . '.xls';

        return Response::make(app(TestResultsExportService::class)->toExcelHtml($test, true), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function safeFilename(string $title): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) ?: 'test';
    }

    private function authorizeTest(Test $test): void
    {
        if ($test->created_by !== auth()->id()) {
            abort(403);
        }
    }
}
