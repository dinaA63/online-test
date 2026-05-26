@extends('layouts.app')
@section('title', 'Результаты теста')

@section('content')
<div class="container py-4">
    <x-page-header :title="$attempt->test->title . ' — результаты'" label="Результаты" />

    <div class="stone-card mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <p class="text-muted small mb-1">Итоговый результат</p>
                <p class="display-6 fw-bold mb-0" style="color: var(--primary);">{{ round($attempt->score, 2) }}%</p>
            </div>
            @if($attempt->pending_manual_review)
                <span class="badge-soft badge-soft-warning">Ожидается ручная проверка текстовых ответов</span>
            @else
                <span class="badge-soft badge-soft-success">Проверка завершена</span>
            @endif
        </div>
    </div>

    @foreach($questions as $question)
        @php
            $questionAnswers = $answers->get($question->id, collect());
            $isCorrect = $scoring->isQuestionCorrect($question, $questionAnswers);
            $firstAnswer = $questionAnswers->first();
        @endphp
        <div class="stone-card mb-3 question-result {{ $isCorrect ? 'question-result--ok' : 'question-result--fail' }}">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <h5 class="fw-semibold mb-0">{{ $question->text }}</h5>
                <span class="badge-soft {{ $question->type === 'text' && $attempt->pending_manual_review ? 'badge-soft-warning' : ($isCorrect ? 'badge-soft-success' : 'badge-soft-danger') }}">
                    @if($question->type === 'text' && $attempt->pending_manual_review)
                        На проверке
                    @elseif($isCorrect)
                        Верно
                    @else
                        Неверно
                    @endif
                </span>
            </div>
            <p class="text-muted small mb-2">{{ $question->type_label }} · {{ $scoring->pointsEarned($question, $questionAnswers) }} / {{ $question->points ?? 1 }} б.</p>

            @if($question->type === 'text' && $attempt->pending_manual_review)
                <p class="mb-0"><strong>Ваш ответ:</strong> {{ $firstAnswer->answer_text ?? 'Нет ответа' }}</p>
            @elseif(!$isCorrect)
                <p class="mb-1"><strong>Ваш ответ:</strong></p>
                @if($question->type === 'single_choice')
                    <p class="mb-0">{{ $firstAnswer?->choice?->text ?? 'Нет ответа' }}</p>
                @elseif($question->type === 'multiple_choice')
                    <p class="mb-0">{{ $questionAnswers->pluck('choice.text')->filter()->join(', ') ?: 'Нет ответа' }}</p>
                @elseif($question->type === 'sequence')
                    @php $order = json_decode($firstAnswer->answer_text ?? '{}', true)['order'] ?? []; @endphp
                    <ol class="mb-0 ps-3">
                        @foreach($order as $id)
                            <li>{{ $question->sequenceItems->firstWhere('id', (int)$id)?->item_text ?? '—' }}</li>
                        @endforeach
                    </ol>
                @elseif($question->type === 'matching')
                    @php $map = json_decode($firstAnswer->answer_text ?? '{}', true) ?: []; @endphp
                    <ul class="mb-0">
                        @foreach($question->matchingPairs as $pair)
                            @php
                                $sel = $map[$pair->id] ?? $map[(string)$pair->id] ?? null;
                                $selText = is_numeric($sel) ? ($question->matchingPairs->firstWhere('id', (int)$sel)?->right_text ?? '—') : $sel;
                            @endphp
                            <li>{{ $pair->left_text }} → {{ $selText ?: '—' }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mb-0">{{ $firstAnswer->answer_text ?? 'Нет ответа' }}</p>
                @endif
            @endif
        </div>
    @endforeach

    <div class="d-flex gap-2 flex-wrap mt-4">
        <a href="{{ route('student.results') }}" class="btn btn-primary btn-pill"><i class="fas fa-list me-2"></i>Все результаты</a>
        <a href="{{ route('student.tests.index') }}" class="btn btn-ghost btn-pill">К тестам</a>
    </div>
</div>
@endsection
