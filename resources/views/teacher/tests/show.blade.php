@extends('layouts.app')
@section('title', $test->title)

@section('content')
<div class="container py-4">
    <x-page-header :title="$test->title" label="Тест">
        <x-slot:actions>
            <a href="{{ route('teacher.tests.edit', $test) }}" class="btn btn-ghost btn-pill"><i class="fas fa-edit"></i></a>
            <a href="{{ route('teacher.tests.statistics', $test) }}" class="btn btn-ghost btn-pill"><i class="fas fa-chart-bar me-1"></i>Статистика</a>
            <div class="dropdown">
                <button class="btn btn-primary btn-pill dropdown-toggle" data-bs-toggle="dropdown">Экспорт</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('teacher.tests.export.csv', $test) }}"><i class="fas fa-file-csv me-2"></i>CSV (сводка)</a></li>
                    <li><a class="dropdown-item" href="{{ route('teacher.tests.export.csv.detailed', $test) }}"><i class="fas fa-file-csv me-2"></i>CSV (по вопросам)</a></li>
                    <li><a class="dropdown-item" href="{{ route('teacher.tests.export.excel', $test) }}"><i class="fas fa-file-excel me-2"></i>Excel (листы по попыткам)</a></li>
                </ul>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="stone-card mb-4">
        <p class="mb-3">{{ $test->description ?: 'Описание не задано' }}</p>
        <div class="row g-3 text-muted small">
            <div class="col-md-4"><i class="fas fa-clock me-2"></i>{{ $test->time_limit ? $test->time_limit.' мин' : 'Без лимита' }}</div>
            <div class="col-md-4"><i class="fas fa-redo me-2"></i>Попыток: {{ $test->max_attempts }}</div>
            <div class="col-md-4"><i class="fas fa-question-circle me-2"></i>Вопросов: {{ $test->questions->count() }}</div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="h5 fw-bold mb-0">Вопросы</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('teacher.questions.create', $test) }}" class="btn btn-primary btn-pill"><i class="fas fa-plus me-1"></i>Добавить</a>
            <a href="{{ route('teacher.gift.import.create') }}?test_id={{ $test->id }}" class="btn btn-ghost btn-pill"><i class="fas fa-file-import me-1"></i>GIFT</a>
        </div>
    </div>

    @forelse($test->questions as $question)
        <div class="stone-card mb-3">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h5 class="fw-semibold mb-2">{{ $question->text }}</h5>
                    <span class="badge-soft badge-soft-info">{{ $question->type_label }}</span>
                    <span class="badge-soft badge-soft-muted">{{ $question->points ?? 1 }} б.</span>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('teacher.questions.edit', $question) }}" class="btn btn-ghost btn-sm btn-pill"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('teacher.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Удалить вопрос?')">@csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm btn-pill"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            @if($question->type === 'sequence')
                <ol class="mt-3 mb-0 ps-3">
                    @foreach($question->sequenceItems->sortBy('correct_order') as $item)
                        <li>{{ $item->item_text }}</li>
                    @endforeach
                </ol>
            @elseif($question->type === 'matching')
                <ul class="mt-3 mb-0 list-unstyled">
                    @foreach($question->matchingPairs as $pair)
                        <li class="mb-1"><span class="fw-semibold">{{ $pair->left_text }}</span> → {{ $pair->right_text }}</li>
                    @endforeach
                </ul>
            @elseif($question->type !== 'text')
                <ul class="mt-3 mb-0">
                    @foreach($question->choices as $choice)
                        <li>{{ $choice->text }} @if($choice->is_correct)<span class="badge-soft badge-soft-success ms-1">верно</span>@endif</li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 mb-0 text-muted small"><strong>Эталон:</strong> {{ $question->correct_text ?: 'Ручная проверка' }}</p>
            @endif
        </div>
    @empty
        <div class="empty-state">Вопросов пока нет. <a href="{{ route('teacher.questions.create', $test) }}">Добавить первый</a></div>
    @endforelse
</div>
@endsection
