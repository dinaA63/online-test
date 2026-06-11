@extends('layouts.app')
@section('title', 'Добавить вопрос')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6"><i class="fas fa-plus-circle me-2" style="color: var(--primary);"></i>Новый вопрос</h1>
        <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>К тесту</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('teacher.questions.store', $test) }}" method="POST" id="questionForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Текст вопроса</label>
                            <textarea name="text" rows="3" class="form-control" required>{{ old('text') }}</textarea>
                            @error('text')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Тип вопроса</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="single_choice" {{ old('type') == 'single_choice' ? 'selected' : '' }}>Одиночный выбор</option>
                                <option value="multiple_choice" {{ old('type') == 'multiple_choice' ? 'selected' : '' }}>Множественный выбор</option>
                                <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Текстовый ответ</option>
                                <option value="matching" {{ old('type') == 'matching' ? 'selected' : '' }}>Соответствие</option>
                                <option value="sequence" {{ old('type') == 'sequence' ? 'selected' : '' }}>Последовательность</option>
                            </select>
                            @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Баллы</label>
                                <input type="number" name="points" class="form-control" value="{{ old('points', 1) }}" min="1">
                                @error('points')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Порядок</label>
                                <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}">
                                @error('order')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Блок для matching -->
                        <div class="mb-3" id="matchingBlock" style="display: none;">
                            <label class="form-label fw-semibold">Пары соответствия</label>
                            <div id="pairsContainer">
                                @php $pairCount = max(count(old('pairs', [])), 2); @endphp
                                @for($i = 0; $i < $pairCount; $i++)
                                <div class="row g-2 mb-2 pair-item align-items-center">
                                    <div class="col-md-5">
                                        <input type="text" name="pairs[{{ $i }}][left_text]" class="form-control" 
                                               value="{{ old("pairs.$i.left_text") }}" placeholder="Левая колонка">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="pairs[{{ $i }}][right_text]" class="form-control" 
                                               value="{{ old("pairs.$i.right_text") }}" placeholder="Правая колонка">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-pair">×</button>
                                    </div>
                                </div>
                                @endfor
                            </div>
                            <button type="button" id="addPair" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить пару</button>
                            @error('pairs')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <!-- Блок для sequence -->
                        <div class="mb-3" id="sequenceBlock" style="display: none;">
                            <label class="form-label fw-semibold">Последовательность (сверху вниз — правильный порядок)</label>
                            <div id="sequenceContainer">
                                @php $seqCount = max(count(old('sequence_items', [])), 2); @endphp
                                @for($i = 0; $i < $seqCount; $i++)
                                <div class="input-group mb-2 sequence-item-row">
                                    <span class="input-group-text">{{ $i + 1 }}</span>
                                    <input type="text" name="sequence_items[{{ $i }}][item_text]" class="form-control" 
                                           value="{{ old("sequence_items.$i.item_text") }}" placeholder="Шаг / этап">
                                    <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
                                </div>
                                @endfor
                            </div>
                            <button type="button" id="addSequence" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить шаг</button>
                            @error('sequence_items')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <!-- Блок для choices (single/multiple) -->
                        <div class="mb-3" id="choicesBlock" style="display: block;">
                            <label class="form-label fw-semibold">Варианты ответов</label>
                            <div id="choicesContainer">
                                @php $choiceCount = max(count(old('choices', [])), 1); @endphp
                                @for($i = 0; $i < $choiceCount; $i++)
                                <div class="input-group mb-2 choice-item">
                                    <input type="text" name="choices[{{ $i }}][text]" class="form-control" 
                                           value="{{ old("choices.$i.text") }}" placeholder="Текст варианта">
                                    <div class="input-group-text">
                                        <input type="checkbox" name="choices[{{ $i }}][is_correct]" value="1" class="form-check-input mt-0"
                                               {{ old("choices.$i.is_correct") ? 'checked' : '' }}>
                                        <label class="form-check-label ms-1">Правильный</label>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger remove-choice">×</button>
                                </div>
                                @endfor
                            </div>
                            <button type="button" id="addChoice" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить вариант</button>
                            @error('choices')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <!-- Блок для текстового ответа -->
                        <div class="mb-3" id="correctTextBlock" style="display: none;">
                            <label class="form-label fw-semibold">Эталонный ответ (необязательно)</label>
                            <textarea name="correct_text" rows="3" class="form-control" 
                                      placeholder="Можно оставить пустым. Проверка текстовых ответов выполняется вручную.">{{ old('correct_text') }}</textarea>
                            <div class="form-text">Ориентир при ручной проверке.</div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Сохранить вопрос</button>
                            <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-secondary">Отмена</a>
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
        const matchingBlock = document.getElementById('matchingBlock');
        const sequenceBlock = document.getElementById('sequenceBlock');
        const correctTextBlock = document.getElementById('correctTextBlock');
        const choicesContainer = document.getElementById('choicesContainer');
        const pairsContainer = document.getElementById('pairsContainer');
        const sequenceContainer = document.getElementById('sequenceContainer');
        const addChoiceBtn = document.getElementById('addChoice');
        const addPairBtn = document.getElementById('addPair');
        const addSequenceBtn = document.getElementById('addSequence');

        function toggleBlocks() {
            const type = typeSelect.value;
            choicesBlock.style.display = (type === 'single_choice' || type === 'multiple_choice') ? 'block' : 'none';
            matchingBlock.style.display = type === 'matching' ? 'block' : 'none';
            sequenceBlock.style.display = type === 'sequence' ? 'block' : 'none';
            correctTextBlock.style.display = type === 'text' ? 'block' : 'none';
        }

        typeSelect.addEventListener('change', toggleBlocks);
        toggleBlocks();

        // Добавить вариант выбора
        addChoiceBtn.addEventListener('click', function() {
            const index = choicesContainer.children.length;
            const div = document.createElement('div');
            div.className = 'input-group mb-2 choice-item';
            div.innerHTML = `
                <input type="text" name="choices[${index}][text]" class="form-control" placeholder="Текст варианта">
                <div class="input-group-text">
                    <input type="checkbox" name="choices[${index}][is_correct]" value="1" class="form-check-input mt-0">
                    <label class="form-check-label ms-1">Правильный</label>
                </div>
                <button type="button" class="btn btn-outline-danger remove-choice">×</button>
            `;
            choicesContainer.appendChild(div);
        });

        // Удалить вариант выбора
        choicesContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-choice')) {
                e.target.closest('.choice-item').remove();
                reindexChoices();
            }
        });

        function reindexChoices() {
            Array.from(choicesContainer.children).forEach((child, idx) => {
                const textInput = child.querySelector('input[type="text"]');
                const checkbox = child.querySelector('input[type="checkbox"]');
                if (textInput) textInput.name = `choices[${idx}][text]`;
                if (checkbox) checkbox.name = `choices[${idx}][is_correct]`;
            });
        }

        // Добавить пару
        addPairBtn.addEventListener('click', function() {
            const index = pairsContainer.children.length;
            const div = document.createElement('div');
            div.className = 'row g-2 mb-2 pair-item align-items-center';
            div.innerHTML = `
                <div class="col-md-5"><input type="text" name="pairs[${index}][left_text]" class="form-control" placeholder="Левая колонка"></div>
                <div class="col-md-5"><input type="text" name="pairs[${index}][right_text]" class="form-control" placeholder="Правая колонка"></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-pair">×</button></div>
            `;
            pairsContainer.appendChild(div);
        });

        // Удалить пару
        pairsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-pair')) {
                e.target.closest('.pair-item').remove();
                reindexPairs();
            }
        });

        function reindexPairs() {
            Array.from(pairsContainer.children).forEach((child, idx) => {
                const inputs = child.querySelectorAll('input');
                if (inputs[0]) inputs[0].name = `pairs[${idx}][left_text]`;
                if (inputs[1]) inputs[1].name = `pairs[${idx}][right_text]`;
            });
        }

        // Добавить шаг последовательности
        addSequenceBtn.addEventListener('click', function() {
            const index = sequenceContainer.children.length;
            const div = document.createElement('div');
            div.className = 'input-group mb-2 sequence-item-row';
            div.innerHTML = `
                <span class="input-group-text">${index + 1}</span>
                <input type="text" name="sequence_items[${index}][item_text]" class="form-control" placeholder="Шаг / этап">
                <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
            `;
            sequenceContainer.appendChild(div);
        });

        // Удалить шаг
        sequenceContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-sequence')) {
                if (sequenceContainer.children.length <= 2) {
                    alert('Минимум 2 шага');
                    return;
                }
                e.target.closest('.sequence-item-row').remove();
                reindexSequence();
            }
        });

        function reindexSequence() {
            Array.from(sequenceContainer.children).forEach((child, idx) => {
                child.querySelector('.input-group-text').textContent = idx + 1;
                child.querySelector('input').name = `sequence_items[${idx}][item_text]`;
            });
        }
    });
</script>
@endpush
@endsection