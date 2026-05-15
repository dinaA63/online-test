@extends('layouts.app')
@section('title', 'Прохождение теста')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $attempt->test->title }}</h2>
    </div>

    @if($attempt->finished_at)
        <div class="alert alert-info">
            <h4>Тест завершён</h4>
            <p>Ваш результат: <strong>{{ round($attempt->score, 2) }}%</strong></p>
            <a href="{{ route('student.tests.index') }}" class="btn btn-primary">Вернуться к тестам</a>
        </div>
    @else
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
                                        <input class="form-check-input choice-radio" type="radio" name="question_{{$question->id}}" value="{{ $choice->id }}" data-question-id="{{ $question->id }}">
                                        <label class="form-check-label">{{ $choice->text }}</label>
                                    </div>
                                @endforeach
                            @elseif($question->type == 'multiple_choice')
                                @foreach($question->choices as $choice)
                                    <div class="form-check">
                                        <input class="form-check-input choice-checkbox" type="checkbox" name="question_{{$question->id}}[]" value="{{ $choice->id }}" data-question-id="{{ $question->id }}">
                                        <label class="form-check-label">{{ $choice->text }}</label>
                                    </div>
                                @endforeach
                            @elseif($question->type == 'text')
                                <textarea class="form-control text-answer" name="question_{{$question->id}}" data-question-id="{{ $question->id }}" rows="3" placeholder="Введите ответ..."></textarea>
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
            function saveAnswer(questionId, data) {
                fetch('{{ route("student.attempt.save_answer", $attempt) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                }).catch(err => console.error('Save error:', err));
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
                    textTimeouts[questionId] = setTimeout(() => {
                        saveAnswer(questionId, {question_id: questionId, answer_text: this.value});
                    }, 500);
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
                            window.location.href = '{{ route("student.results") }}';
                        } else {
                            alert('Ошибка при завершении теста');
                        }
                    });
                }
            });
        </script>
    @endif
</div>
@endsection