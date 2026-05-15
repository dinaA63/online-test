@extends('layouts.app')
@section('title', 'Мои результаты')

@section('content')
<div class="container">
    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6">
            <i class="fas fa-chart-line me-2" style="color: var(--primary);"></i>
            Мои результаты
        </h1>
    </div>

    @php
        $totalTests = $attempts->groupBy('test_id')->count();
        $averageScore = $attempts->avg('score');
    @endphp

    <!-- Карточки статистики -->
    <div class="row mb-5 g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x mb-2" style="color: var(--primary);"></i>
                    <h3 class="fw-bold mb-0">{{ $totalTests }}</h3>
                    <p class="text-muted">Пройдено тестов</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-percent fa-2x mb-2" style="color: var(--primary);"></i>
                    <h3 class="fw-bold mb-0">{{ round($averageScore, 2) }}%</h3>
                    <p class="text-muted">Средний результат</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2" style="color: var(--primary);"></i>
                    <h3 class="fw-bold mb-0">{{ $attempts->count() }}</h3>
                    <p class="text-muted">Всего попыток</p>
                </div>
            </div>
        </div>
    </div>

    @if($attempts->isEmpty())
        <div class="alert alert-info rounded-4 shadow-sm">
            <i class="fas fa-info-circle me-2"></i> Вы ещё не прошли ни одного теста.
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-table me-2" style="color: var(--primary);"></i>Детальная история попыток</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><i class="fas fa-file-alt me-1"></i> Тест</th>
                                <th><i class="fas fa-calendar-alt me-1"></i> Дата завершения</th>
                                <th><i class="fas fa-chart-simple me-1"></i> Результат</th>
                                <th><i class="fas fa-medal me-1"></i> Оценка</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attempts as $attempt)
                                @php
                                    $score = round($attempt->score, 2);
                                    $grade = $score >= 90 ? 'Отлично' : ($score >= 75 ? 'Хорошо' : ($score >= 60 ? 'Удовлетворительно' : 'Неудовлетворительно'));
                                    $badgeClass = $score >= 90 ? 'bg-success' : ($score >= 75 ? 'bg-primary' : ($score >= 60 ? 'bg-warning text-dark' : 'bg-danger'));
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $attempt->test->title }}</td>
                                    <td>{{ $attempt->finished_at->format('d.m.Y H:i') }}</td>
                                    <td style="min-width: 160px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px; border-radius: 1rem;">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $score }}%; background-color: var(--primary);" aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="fw-semibold" style="min-width: 45px;">{{ $score }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">{{ $grade }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Блок с рекомендацией (опционально) -->
        <div class="alert alert-light rounded-4 shadow-sm mt-4 border-start border-4" style="border-left-color: var(--primary) !important;">
            <i class="fas fa-lightbulb me-2" style="color: var(--primary);"></i>
            <strong>Совет:</strong> Чтобы улучшить результаты, попробуйте пройти тесты заново (если разрешено) и обратите внимание на вопросы, где допустили ошибки.
        </div>
    @endif
</div>
@endsection