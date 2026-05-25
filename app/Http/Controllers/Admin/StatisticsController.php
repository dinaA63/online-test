<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Test;
use App\Models\Attempt;
use App\Models\Group;
use Illuminate\Http\Request;
use League\Csv\Writer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        $csv = Writer::createFromString('');
        $csv->insertOne(['Студент', 'Email', 'Тест', 'Результат (%)', 'Дата завершения']);

        foreach ($attempts as $attempt) {
            $csv->insertOne([
                $attempt->user->name,
                $attempt->user->email,
                $attempt->test->title,
                round($attempt->score, 2),
                $attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—'
            ]);
        }

        return response((string) $csv, 200, [
            'Content-Type'           => 'text/csv; charset=UTF-8',
            'Content-Disposition'    => 'attachment; filename="statistics_export.csv"',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = Attempt::whereNotNull('finished_at')->with('user', 'test');
        $this->applyFilters($query, $request);
        $attempts = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Студент');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Тест');
        $sheet->setCellValue('D1', 'Результат (%)');
        $sheet->setCellValue('E1', 'Дата завершения');

        $row = 2;
        foreach ($attempts as $attempt) {
            $sheet->setCellValue('A' . $row, $attempt->user->name);
            $sheet->setCellValue('B' . $row, $attempt->user->email);
            $sheet->setCellValue('C' . $row, $attempt->test->title);
            $sheet->setCellValue('D' . $row, round($attempt->score, 2));
            $sheet->setCellValue('E' . $row, $attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'stat_') . '.xlsx';
        $writer->save($tempFile);

        return response()->download($tempFile, 'statistics_export.xlsx')->deleteFileAfterSend(true);
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