@extends('layouts.app')
@section('title', 'Статистика: '.$test->title)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="display-6"><i class="fas fa-chart-line me-2" style="color: var(--primary);"></i>Статистика теста "{{ $test->title }}"</h1>
        <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-secondary rounded-pill">
            <i class="fas fa-arrow-left me-1"></i>Назад к тесту
        </a>
    </div>

    <!-- Форма фильтра по группам -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('teacher.tests.statistics', $test) }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold"><i class="fas fa-filter me-1"></i>Фильтр по группе</label>
                    <select name="group_id" class="form-select">
                        <option value="">Все пользователи</option>
                        @foreach(\App\Models\Group::all() as $group)
                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        <i class="fas fa-search me-1"></i> Применить
                    </button>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('teacher.tests.export', $test) }}?group_id={{ request('group_id') }}" class="btn btn-outline-success rounded-pill">
                        <i class="fas fa-download me-1"></i> Экспорт CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Карточки статистики -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <i class="fas fa-chart-simple fa-2x mb-2" style="color: var(--primary);"></i>
                    <h3 class="card-title fw-bold">{{ $totalAttempts }}</h3>
                    <p class="card-text text-muted">Всего попыток</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <i class="fas fa-percent fa-2x mb-2" style="color: var(--primary);"></i>
                    <h3 class="card-title fw-bold">{{ round($averageScore, 2) }}%</h3>
                    <p class="card-text text-muted">Средний балл</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <i class="fas fa-redo-alt fa-2x mb-2" style="color: var(--primary);"></i>
                    <h3 class="card-title fw-bold">{{ $test->max_attempts }}</h3>
                    <p class="card-text text-muted">Макс. попыток</p>
                </div>
            </div>
        </div>
    </div>

    <!-- График результатов -->
    @if($scores->count() > 0)
        <div class="card mb-4 border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-line me-2" style="color: var(--primary);"></i>Динамика результатов попыток</h5>
            </div>
            <div class="card-body">
                <canvas id="scoreChart" width="400" height="200"></canvas>
            </div>
        </div>
    @else
        <div class="alert alert-info rounded-4">Нет данных для построения графика.</div>
    @endif

    <!-- Таблица студентов и их средний балл -->
    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-users me-2" style="color: var(--primary);"></i>Результаты студентов</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-user-graduate me-1"></i> Студент</th>
                            <th><i class="fas fa-chart-simple me-1"></i> Средний результат (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentResults as $student => $avg)
                            <tr>
                                <td class="fw-semibold">{{ $student }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px; border-radius: 1rem;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $avg }}%;" aria-valuenow="{{ $avg }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-3 py-2">{{ round($avg, 2) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Нет результатов студентов</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('scoreChart');
        if (canvas && {{ $scores->count() > 0 ? 'true' : 'false' }}) {
            const scores = @json($scores);
            const labels = scores.map((_, i) => 'Попытка ' + (i + 1));
            new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Результат (%)',
                        data: scores,
                        borderColor: '#1E90FF',
                        backgroundColor: 'rgba(30, 144, 255, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#1E90FF',
                        pointBorderColor: '#fff',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { callbacks: { label: (ctx) => ctx.raw.toFixed(2) + '%' } }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, title: { display: true, text: 'Процент правильных ответов' } },
                        x: { title: { display: true, text: 'Номер попытки' } }
                    }
                }
            });
        }
    });
</script>
@endpush