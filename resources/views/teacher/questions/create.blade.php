@extends('layouts.app')
@section('title', 'Добавить вопрос')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2" style="color: var(--primary);"></i>Добавить вопрос в тест "{{ $test->title }}"</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.questions.store', $test) }}" method="POST" id="questionForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Текст вопроса</label>
                            <textarea name="text" rows="3" class="form-control" required>{{ old('text') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Тип вопроса</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="single_choice">Одиночный выбор</option>
                                <option value="multiple_choice">Множественный выбор</option>
                                <option value="text">Текстовый ответ</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Баллы за вопрос</label>
                            <input type="number" name="points" class="form-control" value="{{ old('points', 1) }}" min="1">
                        </div>

                        <div id="choicesBlock" style="display: {{ old('type', 'single_choice') != 'text' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Варианты ответов</label>
                            <div id="choicesContainer">
                                <div class="input-group mb-2 choice-item">
                                    <input type="text" name="choices[0][text]" class="form-control" placeholder="Текст варианта">
                                    <div class="input-group-text">
                                        <input type="checkbox" name="choices[0][is_correct]" value="1" class="form-check-input mt-0">
                                        <label class="form-check-label ms-1">Правильный</label>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger remove-choice">×</button>
                                </div>
                            </div>
                            <button type="button" id="addChoice" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить вариант</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Порядок (необязательно)</label>
                            <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}">
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить вопрос</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const choicesBlock = document.getElementById('choicesBlock');
        const choicesContainer = document.getElementById('choicesContainer');
        const addButton = document.getElementById('addChoice');

        function toggleChoicesBlock() {
            choicesBlock.style.display = typeSelect.value === 'text' ? 'none' : 'block';
        }
        typeSelect.addEventListener('change', toggleChoicesBlock);
        toggleChoicesBlock();

        addButton.addEventListener('click', function() {
            const index = choicesContainer.children.length;
            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2 choice-item';
            newItem.innerHTML = `
                <input type="text" name="choices[${index}][text]" class="form-control" placeholder="Текст варианта">
                <div class="input-group-text">
                    <input type="checkbox" name="choices[${index}][is_correct]" value="1" class="form-check-input mt-0">
                    <label class="form-check-label ms-1">Правильный</label>
                </div>
                <button type="button" class="btn btn-outline-danger remove-choice">×</button>
            `;
            choicesContainer.appendChild(newItem);
        });

        choicesContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-choice') || e.target.parentElement.classList.contains('remove-choice')) {
                e.target.closest('.choice-item').remove();
                Array.from(choicesContainer.children).forEach((child, idx) => {
                    child.querySelector('input[name^="choices["]')?.setAttribute('name', `choices[${idx}][text]`);
                    const cb = child.querySelector('input[type="checkbox"]');
                    if (cb) cb.setAttribute('name', `choices[${idx}][is_correct]`);
                });
            }
        });
    });
</script>
@endpush
@endsection