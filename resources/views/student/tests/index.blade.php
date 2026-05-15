@extends('layouts.app')
@section('title', 'Доступные тесты')

@section('content')
<div class="container">
    <h1 class="mb-4"><i class="fas fa-graduation-cap me-2"></i>Доступные тесты</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        @forelse($tests as $test)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-file-alt me-2"></i>{{ $test->title }}
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted">{{ Str::limit($test->description, 100) }}</p>
                        <div class="mb-2">
                            <span class="badge bg-info">Вопросов: {{ $test->questions->count() }}</span>
                            <span class="badge bg-secondary">Попыток: {{ $attemptsCount[$test->id] ?? 0 }} / {{ $test->max_attempts }}</span>
                        </div>
                        @if($completedTests->contains($test->id))
                            <span class="badge bg-success">Пройден</span>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="{{ route('student.tests.show', $test) }}" class="btn btn-primary w-100">Пройти тест</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">Тестов пока нет.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection