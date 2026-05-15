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
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-hourglass-half me-2" style="color: var(--primary);"></i>
                Попытки, ожидающие проверки
            </h5>
        </div>
        <div class="card-body">
            @forelse($pendingAttempts as $attempt)
                <div class="card mb-3 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title fw-bold">{{ $attempt->test->title }}</h5>
                                <p class="card-text text-muted mb-1">
                                    <i class="fas fa-user-graduate me-1"></i> {{ $attempt->user->name }}
                                </p>
                                <p class="card-text text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> Завершён: {{ $attempt->finished_at->format('d.m.Y H:i') }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                    <i class="fas fa-clock me-1"></i> Ожидает проверки
                                </span>
                            </div>
                            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                <a href="{{ route('teacher.reviews.show', $attempt) }}" class="btn btn-primary rounded-pill">
                                    <i class="fas fa-check-double me-1"></i> Проверить
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center rounded-4">
                    <i class="fas fa-info-circle me-2"></i> Нет попыток, требующих ручной проверки.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection