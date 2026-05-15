@extends('layouts.app')
@section('title', 'Ручная проверка ответов')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6">
            <i class="fas fa-clipboard-list me-2" style="color: var(--primary);"></i>
            Ручная проверка ответов
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 pb-0">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-hourglass-half me-2" style="color: var(--primary);"></i>
                Попытки, ожидающие проверки
            </h5>
            <p class="text-muted small mt-1">
                Здесь отображаются тесты, содержащие вопросы с открытым ответом (эссе) или загруженные файлы.
            </p>
        </div>
        <div class="card-body">
            @if($pendingAttempts->isEmpty())
                <div class="alert alert-info text-center rounded-4 mb-0">
                    <i class="fas fa-check-circle me-2"></i> Нет попыток, требующих проверки.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><i class="fas fa-user-graduate me-1"></i> Студент</th>
                                <th><i class="fas fa-file-alt me-1"></i> Тест</th>
                                <th><i class="fas fa-calendar-alt me-1"></i> Дата завершения</th>
                                <th><i class="fas fa-question-circle me-1"></i> Типов вопросов</th>
                                <th><i class="fas fa-cog me-1"></i> Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingAttempts as $attempt)
                                @php
                                    $essayCount = $attempt->answers()
                                        ->whereHas('question', fn($q) => $q->where('type', 'essay'))
                                        ->count();
                                    $fileCount = $attempt->answers()
                                        ->whereHas('question', fn($q) => $q->where('type', 'file_upload'))
                                        ->count();
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $attempt->user->name }}</td>
                                    <td>{{ $attempt->test->title }}</td>
                                    <td>{{ $attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—' }}</td>
                                    <td>
                                        @if($essayCount > 0)
                                            <span class="badge bg-info me-1">Эссе: {{ $essayCount }}</span>
                                        @endif
                                        @if($fileCount > 0)
                                            <span class="badge bg-secondary">Файлы: {{ $fileCount }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('teacher.reviews.show', $attempt) }}" class="btn btn-sm btn-primary rounded-pill">
                                            <i class="fas fa-eye me-1"></i> Проверить
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection