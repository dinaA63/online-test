@extends('layouts.app')

@section('title', 'Статистика')

@section('content')
<div class="container py-4">
    <x-page-header title="Статистика" label="Администрирование" />

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalStudents }}</div>
                <div class="stat-label">Студентов</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalTeachers }}</div>
                <div class="stat-label">Преподавателей</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalTests }}</div>
                <div class="stat-label">Тестов</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ round($averageScore, 1) }}%</div>
                <div class="stat-label">Средний балл</div>
            </div>
        </div>
    </div>

    <!-- Форма для выбора фильтров и экспорта -->
    <form method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-auto">
            <label for="group_id" class="form-label fw-semibold">Группа</label>
            <select name="group_id" id="group_id" class="form-select">
                <option value="">-- Все группы --</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label for="student_id" class="form-label fw-semibold">Студент</label>
            <select name="student_id" id="student_id" class="form-select">
                <option value="">-- Выберите студента --</option>
                @foreach(\App\Models\User::where('role', 'student')->get() as $student)
                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label for="teacher_id" class="form-label fw-semibold">Преподаватель</label>
            <select name="teacher_id" id="teacher_id" class="form-select">
                <option value="">-- Выберите преподавателя --</option>
                @foreach(\App\Models\User::where('role', 'teacher')->get() as $teacher)
                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Показать</button>
        </div>
        <div class="col-auto ms-auto">
            <a href="{{ route('admin.statistics.export.csv', request()->query()) }}" class="btn btn-outline-info me-2">
                <i class="fas fa-file-csv"></i> CSV
            </a>
            <a href="{{ route('admin.statistics.export.excel', request()->query()) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
        </div>
    </form>

    <!-- Блок статистики по группе -->
    @if($groupStats)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5><i class="fas fa-users me-2"></i>Группа: {{ $groupStats['group']->name }}</h5>
            </div>
            <div class="card-body">
                <p>Количество студентов: <strong>{{ $groupStats['studentCount'] }}</strong></p>
                <p>Средний балл: <strong>{{ round($groupStats['avgScore'], 2) }}%</strong></p>
            </div>
        </div>
    @endif

    <!-- Статистика студента -->
    @if($studentStats)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5><i class="fas fa-user-graduate me-2"></i>Студент: {{ $studentStats['student']->name }}</h5>
            </div>
            <div class="card-body">
                <p>Средний балл: <strong>{{ round($studentStats['avgScore'], 2) }}%</strong></p>
                @if($studentStats['attempts']->isNotEmpty())
                    <table class="table table-sm">
                        <thead><tr><th>Тест</th><th>Результат</th><th>Дата</th></tr></thead>
                        <tbody>
                            @foreach($studentStats['attempts'] as $attempt)
                                <tr>
                                    <td>{{ $attempt->test->title }}</td>
                                    <td>{{ round($attempt->score, 2) }}%</td>
                                    <td>{{ $attempt->finished_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Нет завершённых попыток.</p>
                @endif
            </div>
        </div>
    @endif

    <!-- Статистика преподавателя -->
    @if($teacherStats)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5><i class="fas fa-chalkboard-teacher me-2"></i>Преподаватель: {{ $teacherStats['teacher']->name }}</h5>
            </div>
            <div class="card-body">
                <p>Создано тестов: <strong>{{ $teacherStats['testsCreated'] }}</strong></p>
                <p>Средний балл его студентов: <strong>{{ round($teacherStats['avgTestScore'], 2) }}%</strong></p>
            </div>
        </div>
    @endif
</div>
@endsection