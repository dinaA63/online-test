@extends('layouts.app')
@section('title', 'Результаты теста')

@section('content')
<div class="container">
    <h1 class="mb-4">{{ $attempt->test->title }} — Результаты</h1>
    <div class="alert alert-info">
        <strong>Ваш результат:</strong> {{ round($attempt->score, 2) }}%
    </div>

    @foreach($questions as $question)
        @php
            // Получаем ответы студента на этот вопрос
            $userAnswer = $answers->get($question->id);
            $isCorrect = false;

            if ($question->type === 'single_choice') {
                $correctChoice = $question->choices->firstWhere('is_correct', true);
                $isCorrect = $userAnswer && $userAnswer->choice_id == $correctChoice?->id;
            } elseif ($question->type === 'multiple_choice') {
                $correctChoiceIds = $question->choices->where('is_correct', true)->pluck('id')->sort()->values();
                $userChoiceIds = $attempt->answers()
                    ->where('question_id', $question->id)
                    ->pluck('choice_id')
                    ->sort()
                    ->values();
                $isCorrect = $correctChoiceIds->toArray() == $userChoiceIds->toArray();
            } elseif (in_array($question->type, ['text', 'essay'])) {
                $correctText = $question->correct_text ?? '';
                $userText = $userAnswer->answer_text ?? '';
                // Сравниваем без учёта регистра и пробелов
                $isCorrect = !empty($correctText) && strtolower(trim($userText)) === strtolower(trim($correctText));
            }
        @endphp

        <div class="card mb-3 border-{{ $isCorrect ? 'success' : 'danger' }}">
            <div class="card-body">
                <h5 class="card-title">{{ $question->text }}</h5>
                @if($isCorrect)
                    <span class="badge bg-success">Правильно</span>
                @else
                    <span class="badge bg-danger">Неправильно</span>
                    <p class="mt-2"><strong>Ваш ответ:</strong>
                        @if($question->type === 'single_choice')
                            {{ $userAnswer->choice->text ?? 'Нет ответа' }}
                        @elseif($question->type === 'multiple_choice')
                            @php
                                $selected = $attempt->answers()
                                    ->where('question_id', $question->id)
                                    ->with('choice')
                                    ->get()
                                    ->pluck('choice.text')
                                    ->filter()
                                    ->join(', ');
                            @endphp
                            {{ $selected ?: 'Нет ответа' }}
                        @else
                            {{ $userAnswer->answer_text ?? 'Нет ответа' }}
                        @endif
                    </p>
                    {{-- Правильный ответ НЕ показываем --}}
                @endif
            </div>
        </div>
    @endforeach

    <a href="{{ route('student.results') }}" class="btn btn-primary">
        <i class="fas fa-list me-2"></i> Все результаты
    </a>
</div>
@endsection