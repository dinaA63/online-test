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
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
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
        <a href="{{ route('teacher.questions.create', $test) }}" class="btn btn-success rounded-pill"><i class="fas fa-plus"></i> Добавить вопрос</a>
    </div>

    @forelse($test->questions as $question)
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="card-title fw-bold">{{ $loop->iteration }}. {{ $question->text }}</h5>
                    <div>
                        <a href="{{ route('teacher.questions.edit', $question) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('teacher.questions.destroy', $question) }}" method="POST" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Удалить вопрос?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="mt-2 mb-3">
                    <span class="badge bg-info rounded-pill px-3 py-2">{{ $question->type_label }}</span>
                    <span class="badge bg-secondary rounded-pill px-3 py-2">Баллов: {{ $question->points ?? 1 }}</span>
                </div>

                {{-- ОТОБРАЖЕНИЕ В ЗАВИСИМОСТИ ОТ ТИПА --}}
                @switch($question->type)
                    {{-- Одиночный / множественный / альтернативный --}}
                    @case('single_choice')
                    @case('multiple_choice')
                    @case('alternative')
                        <ul class="mt-2">
                            @foreach($question->choices as $choice)
                                <li>
                                    {{ $choice->text }}
                                    @if($choice->is_correct)
                                        <span class="badge bg-success rounded-pill ms-2">Правильный</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @break

                    {{-- Текстовые (text, completion, essay) --}}
                    @case('text')
                    @case('completion')
                    @case('essay')
                        <div class="alert alert-light border rounded-4 mt-2">
                            <strong>Правильный ответ:</strong>
                            @if($question->type == 'essay')
                                <span class="text-muted">(проверяется преподавателем)</span>
                            @else
                                <code>{{ $question->correct_text ?: 'Не задан' }}</code>
                            @endif
                        </div>
                        @break

                    {{-- Соответствие --}}
                    @case('matching')
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-borderless">
                                <thead>
                                    <tr><th>Левый элемент</th><th>Правый элемент</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($question->matchingPairs as $pair)
                                        <tr><td>{{ $pair->left_text }}</td><td>{{ $pair->right_text }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @break

                    {{-- Последовательность --}}
                    @case('sequence')
                        <ol class="mt-2">
                            @foreach($question->sequenceItems->sortBy('correct_order') as $item)
                                <li>{{ $item->item_text }}</li>
                            @endforeach
                        </ol>
                        @break

                    {{-- Выпадающий список --}}
                    @case('dropdown')
                        <ul class="mt-2">
                            @foreach($question->dropdownOptions as $opt)
                                <li>
                                    {{ $opt->option_text }}
                                    @if($opt->is_correct)
                                        <span class="badge bg-success rounded-pill ms-2">Правильный</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @break

                    {{-- Перетаскивание --}}
                    @case('drag_drop')
                        <div class="table-responsive mt-2">
                            <table class="table table-sm">
                                <thead><tr><th>Элемент</th><th>Целевая зона</th><th>Правильная зона</th></tr></thead>
                                <tbody>
                                    @foreach($question->dragDropItems as $item)
                                        <tr><td>{{ $item->item_text }}</td><td>{{ $item->target_zone }}</td><td>{{ $item->correct_zone }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @break

                    {{-- Заполнение таблицы --}}
                    @case('table_fill')
                        @php $table = $question->tableFill; @endphp
                        @if($table)
                            <div class="table-responsive mt-2">
                                <table class="table table-bordered text-center">
                                    <thead>
                                        <tr>
                                            @foreach(json_decode($table->headers ?? '[]') as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $rows = json_decode($table->rows ?? '[]', true);
                                            $cells = $table->cells->keyBy(function($c){ return $c->row_index.'_'.$c->col_index; });
                                        @endphp
                                        @foreach($rows as $rowIdx => $rowLabel)
                                            <tr>
                                                @foreach(json_decode($table->headers ?? '[]') as $colIdx => $header)
                                                    <td>
                                                        @php $cell = $cells[$rowIdx.'_'.$colIdx] ?? null; @endphp
                                                        {{ $cell ? $cell->expected_answer : '' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        @break

                    {{-- Загрузка файла --}}
                    @case('file_upload')
                        <div class="alert alert-info mt-2">Студент загружает файл. Проверяется преподавателем.</div>
                        @break

                    {{-- Текстовый обычный (на всякий случай) --}}
                    @default
                        <div class="alert alert-secondary mt-2">Тип вопроса: {{ $question->type_label }}</div>
                @endswitch
            </div>
        </div>
    @empty
        <div class="alert alert-warning rounded-4 text-center">Вопросов пока нет. Добавьте первый вопрос!</div>
    @endforelse
</div>
@endsection