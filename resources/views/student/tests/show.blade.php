@extends('layouts.app')
@section('title', $test->title)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">{{ $test->title }}</h4>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ $test->description }}</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-clock me-2"></i> Время: {{ $test->time_limit ? $test->time_limit.' минут' : 'без ограничений' }}</li>
                        <li><i class="fas fa-redo me-2"></i> Максимум попыток: {{ $test->max_attempts }}</li>
                        <li><i class="fas fa-question-circle me-2"></i> Количество вопросов: {{ $test->questions->count() }}</li>
                    </ul>
                    <form action="{{ route('student.attempt.start', $test) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">Начать тест</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection