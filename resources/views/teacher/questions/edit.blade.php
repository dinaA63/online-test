@extends('layouts.app')
@section('title', 'Редактировать вопрос')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h4 class="mb-0"><i class="fas fa-edit me-2" style="color: var(--primary);"></i>Редактирование вопроса</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.questions.update', $question) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Текст вопроса</label>
                            <textarea name="text" rows="3" class="form-control" required>{{ old('text', $question->text) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Тип вопроса</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="single_choice"   {{ old('type', $question->type) == 'single_choice'   ? 'selected' : '' }}>Одиночный выбор</option>
                                <option value="multiple_choice" {{ old('type', $question->type) == 'multiple_choice' ? 'selected' : '' }}>Множественный выбор</option>
                                <option value="text"           {{ old('type', $question->type) == 'text'           ? 'selected' : '' }}>Текстовый ответ</option>
                                <option value="matching"       {{ old('type', $question->type) == 'matching'       ? 'selected' : '' }}>Соответствие</option>
                                <option value="sequence"       {{ old('type', $question->type) == 'sequence'       ? 'selected' : '' }}>Последовательность</option>
                            </select>
                        </div>

                        <div id="sequenceBlock" style="display: {{ old('type', $question->type) === 'sequence' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Элементы последовательности (правильный порядок)</label>
                            <div id="sequenceContainer">
                                @forelse($question->sequenceItems->sortBy('correct_order') as $index => $item)
                                    <div class="input-group mb-2 sequence-item-row">
                                        <span class="input-group-text">{{ $index + 1 }}</span>
                                        <input type="text" name="sequence_items[{{ $index }}][item_text]" class="form-control" value="{{ old("sequence_items.$index.item_text", $item->item_text) }}">
                                        <input type="hidden" name="sequence_items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
                                    </div>
                                @empty
                                    <div class="input-group mb-2 sequence-item-row">
                                        <span class="input-group-text">1</span>
                                        <input type="text" name="sequence_items[0][item_text]" class="form-control">
                                        <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" id="addSequence" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить шаг</button>
                            <input type="hidden" name="deleted_sequence_items" id="deletedSequenceItems" value="">
                        </div>

                        <div id="matchingBlock" style="display: {{ old('type', $question->type) === 'matching' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Пары соответствия</label>
                            <div id="pairsContainer">
                                @forelse($question->matchingPairs as $index => $pair)
                                    <div class="row g-2 mb-2 pair-item">
                                        <div class="col-md-5"><input type="text" name="pairs[{{ $index }}][left_text]" class="form-control" value="{{ old("pairs.$index.left_text", $pair->left_text) }}"></div>
                                        <div class="col-md-5"><input type="text" name="pairs[{{ $index }}][right_text]" class="form-control" value="{{ old("pairs.$index.right_text", $pair->right_text) }}"></div>
                                        <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-pair">×</button></div>
                                        <input type="hidden" name="pairs[{{ $index }}][id]" value="{{ $pair->id }}">
                                    </div>
                                @empty
                                    <div class="row g-2 mb-2 pair-item">
                                        <div class="col-md-5"><input type="text" name="pairs[0][left_text]" class="form-control"></div>
                                        <div class="col-md-5"><input type="text" name="pairs[0][right_text]" class="form-control"></div>
                                        <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-pair">×</button></div>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" id="addPair" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить пару</button>
                            <input type="hidden" name="deleted_pairs" id="deletedPairs" value="">
                        </div>

                        <div id="choicesBlock" style="display: {{ in_array(old('type', $question->type), ['single_choice','multiple_choice']) ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Варианты ответов</label>
                            <div id="choicesContainer">
                                @foreach($question->choices as $index => $choice)
                                    <div class="input-group mb-2 choice-item" data-id="{{ $choice->id }}">
                                        <input type="text" name="choices[{{ $index }}][text]" class="form-control" value="{{ old("choices.$index.text", $choice->text) }}" placeholder="Текст варианта">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="choices[{{ $index }}][is_correct]" value="1" class="form-check-input mt-0" {{ old("choices.$index.is_correct", $choice->is_correct) ? 'checked' : '' }}>
                                            <label class="form-check-label ms-1">Правильный</label>
                                        </div>
                                        <input type="hidden" name="choices[{{ $index }}][id]" value="{{ $choice->id }}">
                                        <button type="button" class="btn btn-outline-danger remove-choice">×</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addChoice" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить вариант</button>
                            <input type="hidden" name="deleted_choices" id="deletedChoices" value="">
                        </div>

                        <div class="mb-3" id="correctTextBlock" style="display: {{ old('type', $question->type) === 'text' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Эталонный ответ (необязательно)</label>
                            <textarea name="correct_text" rows="3" class="form-control" placeholder="Используется как ориентир при ручной проверке.">{{ old('correct_text', $question->correct_text) }}</textarea>
                            <div class="form-text">Текстовые ответы студентов проверяются вручную в личном кабинете преподавателя.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Баллы за вопрос</label>
                            <input type="number" name="points" class="form-control" value="{{ old('points', $question->points ?? 1) }}" min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Порядок</label>
                            <input type="number" name="order" class="form-control" value="{{ old('order', $question->order) }}">
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('teacher.tests.show', $question->test) }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
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
        let deletedIds = [];
        let deletedPairIds = [];
        let deletedSequenceIds = [];

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
                <div class="col-md-5"><input type="text" name="pairs[${index}][left_text]" class="form-control"></div>
                <div class="col-md-5"><input type="text" name="pairs[${index}][right_text]" class="form-control"></div>
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
                <input type="text" name="sequence_items[${index}][item_text]" class="form-control">
                <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
            `;
            sequenceContainer.appendChild(row);
        });

        sequenceContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-sequence')) {
                const row = e.target.closest('.sequence-item-row');
                const idInput = row.querySelector('input[name$="[id]"]');
                if (idInput && idInput.value) {
                    deletedSequenceIds.push(idInput.value);
                    document.getElementById('deletedSequenceItems').value = deletedSequenceIds.join(',');
                }
                if (sequenceContainer.children.length > 1) row.remove();
            }
        });

        pairsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-pair')) {
                const item = e.target.closest('.pair-item');
                const idInput = item.querySelector('input[name$="[id]"]');
                if (idInput && idInput.value) {
                    deletedPairIds.push(idInput.value);
                    document.getElementById('deletedPairs').value = deletedPairIds.join(',');
                }
                if (pairsContainer.children.length > 1) item.remove();
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
                const btn = e.target.closest('.remove-choice');
                const item = btn.closest('.choice-item');
                const idInput = item.querySelector('input[name$="[id]"]');
                if (idInput && idInput.value) {
                    deletedIds.push(idInput.value);
                    document.getElementById('deletedChoices').value = deletedIds.join(',');
                }
                item.remove();
                Array.from(choicesContainer.children).forEach((child, idx) => {
                    child.querySelector('input[name^="choices["]')?.setAttribute('name', `choices[${idx}][text]`);
                    const checkbox = child.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.setAttribute('name', `choices[${idx}][is_correct]`);
                    const hiddenId = child.querySelector('input[name$="[id]"]');
                    if (hiddenId) hiddenId.setAttribute('name', `choices[${idx}][id]`);
                });
            }
        });
    });
</script>
@endpush
@endsection