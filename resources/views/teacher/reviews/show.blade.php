@extends('layouts.app')
@section('title', 'Проверка ответов')

@section('content')
<div class="container py-4">
    <x-page-header :title="$attempt->test->title" label="Проверка">
        <x-slot:actions>
            <a href="{{ route('teacher.reviews.index') }}" class="btn btn-ghost btn-pill"><i class="fas fa-arrow-left me-1"></i>Назад</a>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="stat-card"><div class="stat-value" style="font-size:1rem;">{{ $attempt->user->name }}</div><div class="stat-label">Студент</div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-value" style="font-size:1rem;">{{ $attempt->finished_at?->format('d.m.Y H:i') }}</div><div class="stat-label">Завершён</div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-value">{{ $attempt->score ? round($attempt->score, 1).'%' : '—' }}</div><div class="stat-label">Текущий балл</div></div></div>
    </div>

    <form action="{{ route('teacher.reviews.review', $attempt) }}" method="POST">
        @csrf
        <div class="stone-card">
            @forelse($essayAnswers as $answer)
                <div class="review-item mb-3">
                    <h6 class="fw-semibold mb-2">{{ $answer->question->text }}</h6>
                    <div class="p-3 mb-3" style="background: var(--surface); border: 1px solid var(--border); border-radius: 0.75rem;">
                        {{ $answer->answer_text ?: 'Ответ не предоставлен' }}
                    </div>
                    <label class="form-label fw-semibold">Баллы (0–{{ $answer->question->points ?? 1 }})</label>
                    <input type="number" name="scores[{{ $answer->id }}]" class="form-control" style="max-width: 120px;"
                           value="{{ old("scores.{$answer->id}", $answer->review_score ?? 0) }}"
                           min="0" max="{{ $answer->question->points ?? 1 }}" step="0.5">
                </div>
            @empty
                <div class="empty-state">Нет ответов для проверки</div>
            @endforelse
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary btn-pill" {{ $essayAnswers->isEmpty() ? 'disabled' : '' }}>
                    Сохранить оценки
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
