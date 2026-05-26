@extends('layouts.app')
@section('title', $test->title)

@section('content')
<div class="container py-4">
    <x-page-header :title="$test->title" label="Тест" />
    <div class="stone-card mx-auto" style="max-width: 560px;">
        <p class="text-muted mb-4">{{ $test->description ?: 'Описание не задано' }}</p>
        <ul class="list-unstyled mb-4 text-muted small">
            <li class="mb-2"><i class="fas fa-clock me-2"></i>{{ $test->time_limit ? $test->time_limit.' мин' : 'Без лимита' }}</li>
            <li class="mb-2"><i class="fas fa-redo me-2"></i>Попыток: {{ $test->max_attempts }}</li>
            <li><i class="fas fa-question-circle me-2"></i>Вопросов: {{ $test->questions->count() }}</li>
        </ul>
        <form action="{{ route('student.attempt.start', $test) }}" method="POST">@csrf
            <button type="submit" class="btn btn-primary btn-pill w-100">Начать тест</button>
        </form>
    </div>
</div>
@endsection
