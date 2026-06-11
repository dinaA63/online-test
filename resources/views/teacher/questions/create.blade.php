@extends('layouts.app')
@section('title', 'Добавить вопрос')

@section('content')
<div class="container py-4">
    <x-page-header title="Новый вопрос" :label="$test->title">
        <x-slot:actions>
            <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-ghost btn-pill"><i class="fas fa-arrow-left me-1"></i>К тесту</a>
        </x-slot:actions>
    </x-page-header>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="stone-card">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Не удалось сохранить вопрос:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('teacher.questions.store', $test) }}" method="POST" id="questionForm">
                    @csrf

                    <div class="form-section">
                        <div class="form-section-title">Основное</div>
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

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Баллы</label>
                                <input type="number" name="points" class="form-control" value="{{ old('points', 1) }}" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Порядок</label>
                                <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-section" id="matchingBlock" style="display: {{ old('type') === 'matching' ? 'block' : 'none' }};">
                        <div class="form-section-title">Пары соответствия</div>
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
                            <button type="button" id="addPair" class="btn btn-sm btn-ghost btn-pill mt-2"><i class="fas fa-plus"></i> Добавить пару</button>
                    </div>

                    <div class="form-section" id="sequenceBlock" style="display: none;">
                        <div class="form-section-title">Последовательность (сверху вниз — правильный порядок)</div>
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
                        <button type="button" id="addSequence" class="btn btn-sm btn-ghost btn-pill mt-2"><i class="fas fa-plus"></i> Добавить шаг</button>
                    </div>

                    <div class="form-section" id="choicesBlock" style="display: {{ in_array(old('type', 'single_choice'), ['single_choice','multiple_choice']) ? 'block' : 'none' }};">
                        <div class="form-section-title">Варианты ответов</div>
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
                            <button type="button" id="addChoice" class="btn btn-sm btn-ghost btn-pill mt-2"><i class="fas fa-plus"></i> Добавить вариант</button>
                    </div>

                    <div class="form-section" id="correctTextBlock" style="display: {{ old('type') === 'text' ? 'block' : 'none' }};">
                        <div class="form-section-title">Текстовый ответ</div>
                            <label class="form-label fw-semibold">Эталонный ответ (необязательно)</label>
                            <textarea name="correct_text" rows="3" class="form-control" placeholder="Можно оставить пустым. Проверка текстовых ответов выполняется вручную.">{{ old('correct_text') }}</textarea>
                            <div class="form-text">Ориентир при ручной проверке.</div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-pill">Сохранить вопрос</button>
                        <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-ghost btn-pill">Отмена</a>
                    </div>
                </form>
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

        function setBlockActive(block, active) {
            block.style.display = active ? 'block' : 'none';
            block.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.type !== 'button' && el.type !== 'submit') {
                    el.disabled = !active;
                }
            });
        }

        function toggleBlocks() {
            const type = typeSelect.value;
            const isText = type === 'text';
            const isMatching = type === 'matching';
            const isSequence = type === 'sequence';
            const isChoice = !isText && !isMatching && !isSequence;
            setBlockActive(choicesBlock, isChoice);
            setBlockActive(matchingBlock, isMatching);
            setBlockActive(sequenceBlock, isSequence);
            setBlockActive(correctTextBlock, isText);
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