@extends('layouts.app')
@section('title', 'Результаты теста')

@section('content')
<div class="container">
    <h1 class="mb-4">{{ $attempt->test->title }} — Результаты</h1>
    <div class="alert alert-info">
        <strong>Ваш результат:</strong> {{ round($attempt->score, 2) }}%
        @if($attempt->pending_manual_review)
            <div class="mt-2">
                <span class="badge bg-warning text-dark">Ожидается ручная проверка текстовых ответов</span>
            </div>
        @endif
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
            } elseif ($question->type === 'sequence') {
                $payload = json_decode($userAnswer->answer_text ?? '{}', true);
                $userOrder = $payload['order'] ?? [];
                $correctOrder = $question->sequenceItems->sortBy('correct_order')->pluck('id')->map(fn($id) => (int)$id)->values()->toArray();
                $isCorrect = !empty($userOrder) && array_map('intval', $userOrder) === $correctOrder;
            } elseif ($question->type === 'matching') {
                $map = json_decode($userAnswer->answer_text ?? '{}', true);
                if (is_array($map) && $question->matchingPairs->isNotEmpty()) {
                    $isCorrect = true;
                    foreach ($question->matchingPairs as $pair) {
                        $selected = $map[(string)$pair->id] ?? $map[$pair->id] ?? null;
                        if ($selected === null || mb_strtolower(trim($selected)) !== mb_strtolower(trim($pair->right_text))) {
                            $isCorrect = false;
                            break;
                        }
                    }
                }
            } elseif ($question->type === 'text') {
                $correctText = $question->correct_text ?? '';
                $userText = $userAnswer->answer_text ?? '';
                $isCorrect = !$attempt->pending_manual_review
                    && !empty($correctText)
                    && strtolower(trim($userText)) === strtolower(trim($correctText));
            }
        @endphp

        <div class="card mb-3 border-{{ $isCorrect ? 'success' : 'danger' }}">
            <div class="card-body">
                <h5 class="card-title">{{ $question->text }}</h5>
                @if($question->type === 'text' && $attempt->pending_manual_review)
                    <span class="badge bg-warning text-dark">На ручной проверке</span>
                    <p class="mt-2"><strong>Ваш ответ:</strong> {{ $userAnswer->answer_text ?? 'Нет ответа' }}</p>
                @elseif($isCorrect)
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
                        @elseif($question->type === 'sequence')
                            @php $payload = json_decode($userAnswer->answer_text ?? '{}', true); $order = $payload['order'] ?? []; @endphp
                            <ol class="mb-0">
                                @foreach($order as $id)
                                    <li>{{ $question->sequenceItems->firstWhere('id', (int)$id)?->item_text ?? '—' }}</li>
                                @endforeach
                            </ol>
                        @elseif($question->type === 'matching')
                            @php
                                $map = json_decode($userAnswer->answer_text ?? '{}', true) ?: [];
                            @endphp
                            <ul class="mb-0">
                                @foreach($question->matchingPairs as $pair)
                                    <li>{{ $pair->left_text }} → {{ $map[$pair->id] ?? $map[(string)$pair->id] ?? '—' }}</li>
                                @endforeach
                            </ul>
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