@extends('layouts.app')
@section('title', 'Результаты теста')

@section('content')
<div class="container">
    <h1>{{ $attempt->test->title }} — Результаты</h1>
    <p><strong>Ваш результат:</strong> {{ round($attempt->score, 2) }}%</p>

    @foreach($questions as $question)
        @php
            $userAnswer = $answers->get($question->id);
            $isCorrect = false;
            if ($question->type === 'single_choice') {
                $correctChoice = $question->choices->firstWhere('is_correct', true);
                $isCorrect = $userAnswer && $userAnswer->choice_id == $correctChoice?->id;
            } elseif ($question->type === 'multiple_choice') {
                $correctIds = $question->choices->where('is_correct', true)->pluck('id')->sort()->values();
                $userIds = $question->answersForAttempt($attempt->id)->pluck('choice_id')->sort()->values();
                $isCorrect = $correctIds->toArray() == $userIds->toArray();
            } elseif (in_array($question->type, ['text', 'essay'])) {
                $isCorrect = $userAnswer && strtolower(trim($userAnswer->answer_text)) == strtolower(trim($question->correct_text));
            }
        @endphp
        <div class="card mb-3 {{ $isCorrect ? 'border-success' : 'border-danger' }}">
            <div class="card-body">
                <h5>{{ $question->text }}</h5>
                @if($isCorrect)
                    <span class="badge bg-success">Правильно</span>
                @else
                    <span class="badge bg-danger">Неправильно</span>
                    <p class="mt-2"><strong>Ваш ответ:</strong>
                        @if($userAnswer && $userAnswer->choice)
                            {{ $userAnswer->choice->text }}
                        @elseif($userAnswer && $userAnswer->answer_text)
                            {{ $userAnswer->answer_text }}
                        @else
                            Нет ответа
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endforeach

    <a href="{{ route('student.results') }}" class="btn btn-primary">Мои результаты</a>
</div>
@endsection