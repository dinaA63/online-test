@extends('layouts.app')
@section('title', 'Мои результаты')

@section('content')
<div class="container py-4">
    <x-page-header title="Мои результаты" label="Студент" />

    @php
        $totalTests = $attempts->groupBy('test_id')->count();
        $averageScore = $attempts->avg('score');
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="stat-card"><div class="stat-value">{{ $totalTests }}</div><div class="stat-label">Тестов пройдено</div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-value">{{ round($averageScore ?? 0, 1) }}%</div><div class="stat-label">Средний результат</div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-value">{{ $attempts->count() }}</div><div class="stat-label">Всего попыток</div></div></div>
    </div>

    @if($attempts->isEmpty())
        <div class="empty-state">Вы ещё не завершили ни одного теста</div>
    @else
        <div class="stone-card">
            <div class="table-responsive">
                <table class="table table-minimal mb-0">
                    <thead><tr><th>Тест</th><th>Дата</th><th>Результат</th><th></th></tr></thead>
                    <tbody>
                        @foreach($attempts as $attempt)
                            @php $score = round($attempt->score, 1); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $attempt->test->title }}</td>
                                <td class="text-muted">{{ $attempt->finished_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1"><div class="progress-bar" style="width: {{ min(100, $score) }}%"></div></div>
                                        <span>{{ $score }}%</span>
                                    </div>
                                </td>
                                <td><a href="{{ route('student.attempt.show', $attempt) }}" class="btn btn-ghost btn-sm btn-pill">Открыть</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
