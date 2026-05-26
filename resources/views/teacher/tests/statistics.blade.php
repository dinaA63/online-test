@extends('layouts.app')
@section('title', 'Статистика: '.$test->title)

@section('content')
<div class="container py-4">
    <x-page-header :title="'Статистика: ' . $test->title" label="Аналитика">
        <x-slot:actions>
            <a href="{{ route('teacher.tests.export.csv', $test) }}" class="btn btn-ghost btn-pill"><i class="fas fa-file-csv me-1"></i>CSV</a>
            <a href="{{ route('teacher.tests.export.csv.detailed', $test) }}" class="btn btn-ghost btn-pill">Детальный CSV</a>
            <a href="{{ route('teacher.tests.export.excel', $test) }}" class="btn btn-ghost btn-pill"><i class="fas fa-file-excel me-1"></i>Excel (листы)</a>
            <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-ghost btn-pill"><i class="fas fa-arrow-left"></i></a>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="stat-card"><div class="stat-value">{{ $totalAttempts }}</div><div class="stat-label">Всего попыток</div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-value">{{ round($averageScore, 1) }}%</div><div class="stat-label">Средний балл</div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-value">{{ $test->max_attempts }}</div><div class="stat-label">Макс. попыток</div></div></div>
    </div>

    @if($scores->count() > 0)
        <div class="stone-card mb-4">
            <h2 class="h6 fw-bold mb-3">Динамика попыток</h2>
            <canvas id="scoreChart" height="120"></canvas>
        </div>
    @endif

    <div class="stone-card">
        <h2 class="h6 fw-bold mb-3">Результаты студентов</h2>
        <div class="table-responsive">
            <table class="table table-minimal mb-0">
                <thead><tr><th>Студент</th><th>Средний %</th></tr></thead>
                <tbody>
                    @forelse($studentResults as $student => $avg)
                        <tr>
                            <td class="fw-semibold">{{ $student }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1"><div class="progress-bar" style="width: {{ min(100, $avg) }}%"></div></div>
                                    <span class="badge-soft badge-soft-info">{{ round($avg, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center">Нет данных</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($scores->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scores = @json($scores);
    new Chart(document.getElementById('scoreChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: scores.map((_, i) => 'Попытка ' + (i + 1)),
            datasets: [{
                label: 'Результат (%)',
                data: scores,
                borderColor: '#1E90FF',
                backgroundColor: 'rgba(30, 144, 255, 0.08)',
                tension: 0.25,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: { y: { min: 0, max: 100 } }
        }
    });
});
</script>
@endif
@endpush
