@extends('layouts.app')
@section('title', 'Проверка ответов студента')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6">
            <i class="fas fa-user-check me-2" style="color: var(--primary);"></i>
            Проверка: {{ $attempt->test->title }}
        </h1>
        <a href="{{ route('teacher.reviews.index') }}" class="btn btn-secondary rounded-pill">
            <i class="fas fa-arrow-left me-1"></i> Назад к списку
        </a>
    </div>

    <!-- Информация о студенте и попытке -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-graduate fa-2x mb-2" style="color: var(--primary);"></i>
                    <h5 class="card-title fw-semibold">{{ $attempt->user->name }}</h5>
                    <p class="card-text text-muted small">{{ $attempt->user->email }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-2x mb-2" style="color: var(--primary);"></i>
                    <p class="mb-1">Завершён:</p>
                    <strong>{{ $attempt->finished_at ? $attempt->finished_at->format('d.m.Y H:i') : '—' }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-simple fa-2x mb-2" style="color: var(--primary);"></i>
                    <p class="mb-1">Текущий балл:</p>
                    <strong class="fs-4">{{ $attempt->score ? round($attempt->score, 2) . '%' : '—' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Форма проверки -->
    <form action="{{ route('teacher.reviews.review', $attempt) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-edit me-2" style="color: var(--primary);"></i>
                    Ответы, требующие проверки
                </h5>
            </div>
            <div class="card-body">
                @forelse($essayAnswers as $answer)
                    <div class="review-item mb-4 p-3 bg-light rounded-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1 me-3">
                                <h6 class="fw-semibold mb-2">
                                    <i class="fas fa-question-circle me-1" style="color: var(--primary);"></i>
                                    Вопрос: {{ $answer->question->text }}
                                </h6>
                                <div class="bg-white p-3 rounded-3 border">
                                    <p class="mb-0"><strong>Ответ студента:</strong></p>
                                    @if($answer->question->type == 'file_upload' && $answer->answer_text)
                                        <a href="{{ Storage::url($answer->answer_text) }}" target="_blank" class="btn btn-sm btn-outline-info mt-2">
                                            <i class="fas fa-download me-1"></i> Скачать файл
                                        </a>
                                    @else
                                        <p class="mt-2 mb-0">{{ $answer->answer_text ?: 'Ответ не предоставлен' }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="score-box" style="min-width: 150px;">
                                <label class="form-label fw-semibold">Баллы (0–{{ $answer->question->points ?? 1 }}):</label>
                                <input type="number" name="scores[{{ $answer->id }}]"
                                       class="form-control rounded-pill @error("scores.{$answer->id}") is-invalid @enderror"
                                       value="{{ old("scores.{$answer->id}", $answer->review_score ?? 0) }}"
                                       min="0" max="{{ $answer->question->points ?? 1 }}" step="1">
                                <div class="form-text small">Максимум: {{ $answer->question->points ?? 1 }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning rounded-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> Нет вопросов, требующих ручной проверки.
                    </div>
                @endforelse
            </div>
            <div class="card-footer bg-white border-0 pb-4 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-save me-2"></i> Сохранить оценки и завершить проверку
                </button>
            </div>
        </div>
    </form>
</div>
@endsection