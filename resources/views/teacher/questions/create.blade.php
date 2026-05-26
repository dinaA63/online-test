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
                                <option value="matching">Соответствие</option>
                                <option value="sequence">Последовательность</option>
                            </select>
                        </div>

                        <div id="sequenceBlock" style="display: none;">
                            <label class="form-label fw-semibold">Элементы последовательности (в правильном порядке сверху вниз)</label>
                            <div id="sequenceContainer">
                                <div class="input-group mb-2 sequence-item-row">
                                    <span class="input-group-text">1</span>
                                    <input type="text" name="sequence_items[0][item_text]" class="form-control" placeholder="Шаг / этап">
                                    <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
                                </div>
                                <div class="input-group mb-2 sequence-item-row">
                                    <span class="input-group-text">2</span>
                                    <input type="text" name="sequence_items[1][item_text]" class="form-control" placeholder="Шаг / этап">
                                    <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
                                </div>
                            </div>
                            <button type="button" id="addSequence" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить шаг</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Баллы за вопрос</label>
                            <input type="number" name="points" class="form-control" value="{{ old('points', 1) }}" min="1">
                        </div>

                        <div id="matchingBlock" style="display: {{ old('type') === 'matching' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Пары соответствия</label>
                            <div id="pairsContainer">
                                <div class="row g-2 mb-2 pair-item">
                                    <div class="col-md-5"><input type="text" name="pairs[0][left_text]" class="form-control" placeholder="Левая колонка"></div>
                                    <div class="col-md-5"><input type="text" name="pairs[0][right_text]" class="form-control" placeholder="Правая колонка"></div>
                                    <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-pair">×</button></div>
                                </div>
                                <div class="row g-2 mb-2 pair-item">
                                    <div class="col-md-5"><input type="text" name="pairs[1][left_text]" class="form-control" placeholder="Левая колонка"></div>
                                    <div class="col-md-5"><input type="text" name="pairs[1][right_text]" class="form-control" placeholder="Правая колонка"></div>
                                    <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-pair">×</button></div>
                                </div>
                            </div>
                            <button type="button" id="addPair" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить пару</button>
                        </div>

                        <div id="choicesBlock" style="display: {{ in_array(old('type', 'single_choice'), ['single_choice','multiple_choice']) ? 'block' : 'none' }};">
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

                        <div class="mb-3" id="correctTextBlock" style="display: {{ old('type') === 'text' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Эталонный ответ (необязательно)</label>
                            <textarea name="correct_text" rows="3" class="form-control" placeholder="Можно оставить пустым. Проверка текстовых ответов выполняется вручную.">{{ old('correct_text') }}</textarea>
                            <div class="form-text">Нужен как ориентир при проверке преподавателем.</div>
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
        const correctTextBlock = document.getElementById('correctTextBlock');
        const matchingBlock = document.getElementById('matchingBlock');
        const sequenceBlock = document.getElementById('sequenceBlock');
        const pairsContainer = document.getElementById('pairsContainer');
        const sequenceContainer = document.getElementById('sequenceContainer');
        const addPairBtn = document.getElementById('addPair');
        const addSequenceBtn = document.getElementById('addSequence');

        function toggleBlocks() {
            const type = typeSelect.value;
            const isText = type === 'text';
            const isMatching = type === 'matching';
            const isSequence = type === 'sequence';
            choicesBlock.style.display = (!isText && !isMatching && !isSequence) ? 'block' : 'none';
            matchingBlock.style.display = isMatching ? 'block' : 'none';
            sequenceBlock.style.display = isSequence ? 'block' : 'none';
            correctTextBlock.style.display = isText ? 'block' : 'none';
        }
        typeSelect.addEventListener('change', toggleBlocks);
        toggleBlocks();

        addPairBtn.addEventListener('click', function() {
            const index = pairsContainer.children.length;
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 pair-item';
            row.innerHTML = `
                <div class="col-md-5"><input type="text" name="pairs[${index}][left_text]" class="form-control" placeholder="Левая колонка"></div>
                <div class="col-md-5"><input type="text" name="pairs[${index}][right_text]" class="form-control" placeholder="Правая колонка"></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-pair">×</button></div>
            `;
            pairsContainer.appendChild(row);
        });

        addSequenceBtn.addEventListener('click', function() {
            const index = sequenceContainer.children.length;
            const row = document.createElement('div');
            row.className = 'input-group mb-2 sequence-item-row';
            row.innerHTML = `
                <span class="input-group-text">${index + 1}</span>
                <input type="text" name="sequence_items[${index}][item_text]" class="form-control" placeholder="Шаг / этап">
                <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
            `;
            sequenceContainer.appendChild(row);
        });

        sequenceContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-sequence')) {
                if (sequenceContainer.children.length <= 2) return;
                e.target.closest('.sequence-item-row').remove();
                Array.from(sequenceContainer.children).forEach((child, idx) => {
                    child.querySelector('.input-group-text').textContent = idx + 1;
                    child.querySelector('input').setAttribute('name', `sequence_items[${idx}][item_text]`);
                });
            }
        });

        pairsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-pair')) {
                if (pairsContainer.children.length <= 2) return;
                e.target.closest('.pair-item').remove();
                Array.from(pairsContainer.children).forEach((child, idx) => {
                    child.querySelectorAll('input').forEach((input, i) => {
                        const field = i === 0 ? 'left_text' : 'right_text';
                        input.setAttribute('name', `pairs[${idx}][${field}]`);
                    });
                });
            }
        });

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