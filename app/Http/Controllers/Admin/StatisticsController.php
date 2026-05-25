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

    // BOM для корректной кириллицы
    fwrite($output, "\xEF\xBB\xBF");
    // Директива для Excel – явно указываем разделитель
    fwrite($output, "sep=;\n");
    // Заголовки – разделяем точкой с запятой
    fwrite($output, "Студент;Email;Тест;Результат (%);Дата завершения\n");

    foreach ($attempts as $attempt) {
        $line = [
            $attempt->user->name,
            $attempt->user->email,
            $attempt->test->title,
            round($attempt->score, 2),
            $attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—'
        ];
        // Экранируем значения, чтобы не сломать CSV
        $escaped = array_map(function ($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $line);
        fwrite($output, implode(';', $escaped) . "\n");
    }

    rewind($output);
    $csvContent = stream_get_contents($output);
    fclose($output);

    return response($csvContent, 200, [
        'Content-Type'           => 'text/csv; charset=UTF-8',
        'Content-Disposition'    => 'attachment; filename="statistics_export.csv"',
    ]);
}

    public function exportExcel(Request $request)
    {
        $query = Attempt::whereNotNull('finished_at')->with('user', 'test');
        $this->applyFilters($query, $request);
        $attempts = $query->get();

        // Создаём XML в формате Microsoft Excel 2003 (SpreadsheetML)
        // Это гарантирует правильное отображение столбцов и кириллицы
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Worksheet ss:Name="Export">' . "\n";
        $xml .= '<Table>' . "\n";

        // Заголовки
        $xml .= '<Row>';
        foreach (['Студент', 'Email', 'Тест', 'Результат (%)', 'Дата завершения'] as $header) {
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($header, ENT_XML1, 'UTF-8') . '</Data></Cell>';
        }
        $xml .= '</Row>' . "\n";

        // Данные
        foreach ($attempts as $attempt) {
            $xml .= '<Row>';
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($attempt->user->name, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($attempt->user->email, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($attempt->test->title, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            $xml .= '<Cell><Data ss:Type="Number">' . round($attempt->score, 2) . '</Data></Cell>';
            $xml .= '<Cell><Data ss:Type="String">' . ($attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—') . '</Data></Cell>';
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>';

        return response($xml, 200, [
            'Content-Type'           => 'application/vnd.ms-excel; charset=UTF-8',
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