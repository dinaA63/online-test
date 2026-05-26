@extends('layouts.app')
@section('title', 'Ручная проверка')

@section('content')
<div class="container py-4">
    <x-page-header title="Ручная проверка" label="Преподаватель">
        <x-slot:actions>
            <span class="badge-soft badge-soft-warning">Ожидают: {{ $pendingAttempts->count() }}</span>
        </x-slot:actions>
    </x-page-header>

    @forelse($pendingAttempts as $attempt)
        <div class="stone-card mb-3">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <h5 class="fw-bold mb-1">{{ $attempt->test->title }}</h5>
                    <p class="text-muted small mb-0">{{ $attempt->user->name }} · {{ $attempt->finished_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-5 text-md-end">
                    <a href="{{ route('teacher.reviews.show', $attempt) }}" class="btn btn-primary btn-pill">
                        <i class="fas fa-check-double me-1"></i>Проверить
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">Нет попыток на проверке</div>
    @endforelse
</div>
@endsection
