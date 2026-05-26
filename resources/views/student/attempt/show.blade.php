@extends('layouts.app')
@section('title', 'Прохождение теста')
@if(isset($remainingSeconds) && $remainingSeconds !== null)
    <div class="alert alert-info text-center" id="timer">
        Оставшееся время: <span id="timer-minutes">{{ floor($remainingSeconds / 60) }}</span>:<span id="timer-seconds">{{ $remainingSeconds % 60 }}</span>
    </div>
    <script>
        let totalSeconds = {{ $remainingSeconds }};
        const timerInterval = setInterval(() => {
            totalSeconds--;
            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                document.getElementById('submit-test').click();
            } else {
                document.getElementById('timer-minutes').textContent = Math.floor(totalSeconds / 60);
                document.getElementById('timer-seconds').textContent = (totalSeconds % 60).toString().padStart(2, '0');
            }
        }, 1000);
    </script>
@endif
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $attempt->test->title }}</h2>
        <div class="text-end">
            <div class="small text-muted">Прогресс</div>
            <div class="fw-semibold"><span id="answered-count">0</span> / {{ $questions->count() }}</div>
        </div>
    </div>
    <div class="mb-3">
        <div class="progress" role="progressbar" aria-label="Прогресс прохождения теста">
            <div class="progress-bar" id="testProgressBar" style="width: 0%">0%</div>
        </div>
    </div>

    @if($attempt->finished_at)
        <div class="alert alert-info">
            <h4>Тест завершён</h4>
            <p>Ваш результат: <strong>{{ round($attempt->score, 2) }}%</strong></p>
            @if($attempt->pending_manual_review)
                <p class="mb-0"><strong>Часть ответа ожидает ручной проверки преподавателем.</strong></p>
            @endif
            <a href="{{ route('student.tests.index') }}" class="btn btn-primary">Вернуться к тестам</a>
        </div>
    @else
        <div class="alert alert-light border d-flex justify-content-between align-items-center">
            <span><i class="fas fa-save me-2"></i>Ответы сохраняются автоматически</span>
            <span class="text-muted small" id="saveStatus">Изменений пока нет</span>
        </div>
        <div id="test-form">
            @csrf
            @foreach($questions as $question)
                <div class="card question-card mb-4" data-question-id="{{ $question->id }}">
                    <div class="card-header">
                        <strong><i class="fas fa-question-circle me-2"></i>Вопрос {{ $loop->iteration }}</strong>
                        <span class="badge bg-secondary float-end">{{ $question->type_label }}</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $question->text }}</p>
                        <div class="option-group">
                            @if($question->type == 'single_choice')
                                @foreach($question->choices as $choice)
                                    <div class="form-check">
                                        <input class="form-check-input choice-radio" type="radio" name="question_{{$question->id}}" value="{{ $choice->id }}" data-question-id="{{ $question->id }}"
                                            {{ optional($savedAnswers->get($question->id))->choice_id == $choice->id ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ $choice->text }}</label>
                                    </div>
                                @endforeach
                            @elseif($question->type == 'multiple_choice')
                                @php
                                    $selectedChoices = $allSavedAnswers->where('question_id', $question->id)->pluck('choice_id')->toArray();
                                @endphp
                                @foreach($question->choices as $choice)
                                    <div class="form-check">
                                        <input class="form-check-input choice-checkbox" type="checkbox" name="question_{{$question->id}}[]" value="{{ $choice->id }}" data-question-id="{{ $question->id }}"
                                            {{ in_array($choice->id, $selectedChoices, true) ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ $choice->text }}</label>
                                    </div>
                                @endforeach
                            @elseif($question->type == 'text')
                                <textarea class="form-control text-answer" name="question_{{$question->id}}" data-question-id="{{ $question->id }}" rows="3" placeholder="Введите ответ...">{{ old("question_{$question->id}", optional($savedAnswers->get($question->id))->answer_text) }}</textarea>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="text-center mt-4">
                <button type="button" id="submit-test" class="btn btn-primary btn-lg">Завершить тест</button>
            </div>
        </div>

        <script>
            const saveStatus = document.getElementById('saveStatus');

            function setSaveStatus(text, muted = true) {
                saveStatus.textContent = text;
                saveStatus.classList.toggle('text-muted', muted);
            }

            function saveAnswer(questionId, data) {
                setSaveStatus('Сохранение...', false);
                fetch('{{ route("student.attempt.save_answer", $attempt) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(() => {
                    setSaveStatus('Сохранено');
                    updateProgress();
                })
                .catch(err => {
                    setSaveStatus('Ошибка сохранения', false);
                    console.error('Save error:', err);
                });
            }

            function updateProgress() {
                const total = {{ $questions->count() }};
                let answered = 0;
                document.querySelectorAll('.question-card').forEach(card => {
                    const questionId = card.dataset.questionId;
                    const hasRadio = !!document.querySelector(`.choice-radio[data-question-id="${questionId}"]:checked`);
                    const hasChecks = document.querySelectorAll(`.choice-checkbox[data-question-id="${questionId}"]:checked`).length > 0;
                    const textArea = document.querySelector(`.text-answer[data-question-id="${questionId}"]`);
                    const hasText = textArea ? textArea.value.trim().length > 0 : false;
                    if (hasRadio || hasChecks || hasText) {
                        answered++;
                    }
                });

                const percent = total > 0 ? Math.round((answered / total) * 100) : 0;
                const bar = document.getElementById('testProgressBar');
                const answeredCount = document.getElementById('answered-count');
                bar.style.width = `${percent}%`;
                bar.textContent = `${percent}%`;
                answeredCount.textContent = answered;
            }

            // Одиночный выбор
            document.querySelectorAll('.choice-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    saveAnswer(this.dataset.questionId, {question_id: this.dataset.questionId, choice_id: this.value});
                });
            });

            // Множественный выбор
            document.querySelectorAll('.choice-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    let questionId = this.dataset.questionId;
                    let checked = Array.from(document.querySelectorAll(`.choice-checkbox[data-question-id="${questionId}"]:checked`)).map(cb => cb.value);
                    saveAnswer(questionId, {question_id: questionId, choice_ids: checked});
                });
            });

            // Текстовый ответ с debounce
            let textTimeouts = {};
            document.querySelectorAll('.text-answer').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    let questionId = this.dataset.questionId;
                    clearTimeout(textTimeouts[questionId]);
                    setSaveStatus('Изменения не сохранены', false);
                    textTimeouts[questionId] = setTimeout(() => {
                        saveAnswer(questionId, {question_id: questionId, answer_text: this.value});
                    }, 500);
                    updateProgress();
                });
            });

            // Завершение теста
            document.getElementById('submit-test').addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите завершить тест?')) {
                    fetch('{{ route("student.attempt.submit", $attempt) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({})
                    }).then(response => response.json()).then(data => {
                        if (data.success) {
                            if (data.pending_manual_review) {
                                alert('Тест отправлен. Есть текстовые ответы, они будут проверены преподавателем вручную.');
                            }
                            window.location.href = '{{ route("student.attempt.show", $attempt) }}';
                        } else {
                            alert('Ошибка при завершении теста');
                        }
                    });
                }
            });

            updateProgress();
        </script>
    @endif
</div>
@endsection