@extends('layouts.app')
@section('title', $test->title)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6">{{ $test->title }}</h1>
        <div>
            <a href="{{ route('teacher.tests.edit', $test) }}" class="btn btn-outline-secondary"><i class="fas fa-edit"></i> Редактировать</a>
            <a href="{{ route('teacher.tests.statistics', $test) }}" class="btn btn-info"><i class="fas fa-chart-bar"></i> Статистика</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <p class="card-text">{{ $test->description ?: 'Описание отсутствует' }}</p>
            <div class="row mt-3">
                <div class="col-md-4"><i class="fas fa-clock me-2"></i>Время: {{ $test->time_limit ? $test->time_limit.' мин' : 'без ограничений' }}</div>
                <div class="col-md-4"><i class="fas fa-redo me-2"></i>Попыток: {{ $test->max_attempts }}</div>
                <div class="col-md-4"><i class="fas fa-question-circle me-2"></i>Вопросов: {{ $test->questions->count() }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-list me-2"></i>Вопросы</h3>
        <a href="{{ route('teacher.questions.create', $test) }}" class="btn btn-success"><i class="fas fa-plus"></i> Добавить вопрос</a>
    </div>

    @forelse($test->questions as $question)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title">{{ $question->text }}</h5>
                    <div>
                        <a href="{{ route('teacher.questions.edit', $question) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('teacher.questions.destroy', $question) }}" method="POST" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить вопрос?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-info">{{ $question->type_label }}</span>
                </div>
                @if($question->type != 'text')
                    <ul class="mt-3">
                        @foreach($question->choices as $choice)
                            <li>{{ $choice->text }} @if($choice->is_correct) <span class="badge bg-success">Правильный</span> @endif</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-warning">Вопросов пока нет. Добавьте первый вопрос!</div>
    @endforelse
</div>
@endsection