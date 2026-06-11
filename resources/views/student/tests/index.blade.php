@extends('layouts.app')
@section('title', 'Доступные тесты')

@section('content')
<div class="container py-4">
    <x-page-header title="Доступные тесты" label="Студент" />

    <div class="row g-4">
        @forelse($tests as $test)
            <div class="col-md-6 col-lg-4">
                <div class="tile-card">
                    <div class="tile-card-header">{{ $test->title }}</div>
                    <div class="tile-card-body">
                        <p class="mb-3">{{ Str::limit($test->description, 100) ?: 'Без описания' }}</p>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge-soft badge-soft-info">Вопросов: {{ $test->questions_count }}</span>
                            @if($test->group)
                                <span class="badge-soft badge-soft-muted">{{ $test->group->name }}</span>
                            @endif
                            <span class="badge-soft badge-soft-muted">Попыток: {{ $attemptsCount[$test->id] ?? 0 }}/{{ $test->max_attempts }}</span>
                            @if($completedTests->contains($test->id))
                                <span class="badge-soft badge-soft-success">Пройден</span>
                            @endif
                            @if($pendingManualReviewTests->contains($test->id))
                                <span class="badge-soft badge-soft-warning">На проверке</span>
                            @endif
                        </div>
                    </div>
                    <div class="tile-card-footer">
                        <a href="{{ route('student.tests.show', $test) }}" class="btn btn-primary btn-pill w-100">
                            {{ $completedTests->contains($test->id) ? 'Открыть' : 'Пройти тест' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="empty-state">Тестов пока нет</div></div>
        @endforelse
    </div>
</div>
@endsection
