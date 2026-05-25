<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Test;
use App\Models\Attempt;
use App\Models\Group;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $groups = Group::all();

        $totalStudents   = User::where('role', 'student')->count();
        $totalTeachers   = User::where('role', 'teacher')->count();
        $totalTests      = Test::count();
        $totalAttempts   = Attempt::count();
        $averageScore    = Attempt::whereNotNull('finished_at')->avg('score');

        $selectedGroup = $request->input('group_id');
        $groupStats = null;
        if ($selectedGroup) {
            $group = Group::with('users')->find($selectedGroup);
            if ($group) {
                $groupStudentIds = $group->users->where('role', 'student')->pluck('id');
                $groupStats = [
                    'group'        => $group,
                    'studentCount' => $groupStudentIds->count(),
                    'avgScore'     => Attempt::whereIn('user_id', $groupStudentIds)
                                             ->whereNotNull('finished_at')
                                             ->avg('score'),
                ];
            }
        }

        $selectedStudent = $request->input('student_id');
        $studentStats = null;
        if ($selectedStudent) {
            $student = User::find($selectedStudent);
            if ($student) {
                $studentStats = [
                    'student'   => $student,
                    'attempts'  => Attempt::where('user_id', $student->id)
                                         ->whereNotNull('finished_at')
                                         ->with('test')
                                         ->get(),
                    'avgScore'  => Attempt::where('user_id', $student->id)
                                         ->whereNotNull('finished_at')
                                         ->avg('score'),
                ];
            }
        }

        $selectedTeacher = $request->input('teacher_id');
        $teacherStats = null;
        if ($selectedTeacher) {
            $teacher = User::find($selectedTeacher);
            if ($teacher) {
                $testIds = Test::where('created_by', $teacher->id)->pluck('id');
                $teacherStats = [
                    'teacher'       => $teacher,
                    'testsCreated'  => $testIds->count(),
                    'avgTestScore'  => Attempt::whereIn('test_id', $testIds)
                                             ->whereNotNull('finished_at')
                                             ->avg('score'),
                ];
            }
        }

        return view('admin.statistics.index', compact(
            'groups',
            'totalStudents',
            'totalTeachers',
            'totalTests',
            'totalAttempts',
            'averageScore',
            'selectedGroup',
            'groupStats',
            'selectedStudent',
            'studentStats',
            'selectedTeacher',
            'teacherStats'
        ));
    }

public function exportCsv(Request $request)
{
    $query = Attempt::whereNotNull('finished_at')->with('user', 'test');
    $this->applyFilters($query, $request);
    $attempts = $query->get();

    $output = fopen('php://temp', 'r+');

    // Заголовки в UTF-16LE с BOM
    $headers = ['Студент', 'Email', 'Тест', 'Результат (%)', 'Дата завершения'];
    fwrite($output, "\xFF\xFE"); // BOM UTF-16LE
    fwrite($output, mb_convert_encoding(implode(';', $headers) . "\n", 'UTF-16LE', 'UTF-8'));

    foreach ($attempts as $attempt) {
        $line = [
            $attempt->user->name,
            $attempt->user->email,
            $attempt->test->title,
            round($attempt->score, 2),
            $attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—'
        ];
        fwrite($output, mb_convert_encoding(implode(';', $line) . "\n", 'UTF-16LE', 'UTF-8'));
    }

    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);

    return response($csv, 200, [
        'Content-Type'           => 'text/csv; charset=UTF-16LE',
        'Content-Disposition'    => 'attachment; filename="statistics_export.csv"',
    ]);
}

public function exportExcel(Request $request)
{
    $query = Attempt::whereNotNull('finished_at')->with('user', 'test');
    $this->applyFilters($query, $request);
    $attempts = $query->get();

    $output = fopen('php://temp', 'r+');

    // Заголовки в UTF-16LE с BOM
    $headers = ['Студент', 'Email', 'Тест', 'Результат (%)', 'Дата завершения'];
    fwrite($output, "\xFF\xFE"); // BOM UTF-16LE
    fwrite($output, mb_convert_encoding(implode(';', $headers) . "\n", 'UTF-16LE', 'UTF-8'));

    foreach ($attempts as $attempt) {
        $line = [
            $attempt->user->name,
            $attempt->user->email,
            $attempt->test->title,
            round($attempt->score, 2),
            $attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—'
        ];
        fwrite($output, mb_convert_encoding(implode(';', $line) . "\n", 'UTF-16LE', 'UTF-8'));
    }

    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);

    return response($csv, 200, [
        'Content-Type'           => 'application/vnd.ms-excel; charset=UTF-16LE',
        'Content-Disposition'    => 'attachment; filename="statistics_export.xls"',
    ]);
}


    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('group_id')) {
            $group = Group::find($request->group_id);
            if ($group) {
                $userIds = $group->users->pluck('id');
                $query->whereIn('user_id', $userIds);
            }
        }
        if ($request->filled('student_id')) {
            $query->where('user_id', $request->student_id);
        }
        if ($request->filled('teacher_id')) {
            $testIds = Test::where('created_by', $request->teacher_id)->pluck('id');
            $query->whereIn('test_id', $testIds);
        }
    }
}