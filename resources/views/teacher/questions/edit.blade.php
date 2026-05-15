@extends('layouts.app')
@section('title', 'Редактировать вопрос')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h4 class="mb-0"><i class="fas fa-edit me-2" style="color: var(--primary);"></i>Редактирование вопроса</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.questions.update', $question) }}" method="POST" id="questionForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Текст вопроса -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Текст вопроса <span class="text-danger">*</span></label>
                            <textarea name="text" rows="3" class="form-control @error('text') is-invalid @enderror" required>{{ old('text', $question->text) }}</textarea>
                            @error('text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Тип вопроса -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Тип вопроса</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="single_choice"   {{ old('type', $question->type) == 'single_choice'   ? 'selected' : '' }}>Одиночный выбор</option>
                                <option value="multiple_choice" {{ old('type', $question->type) == 'multiple_choice' ? 'selected' : '' }}>Множественный выбор</option>
                                <option value="alternative"     {{ old('type', $question->type) == 'alternative'     ? 'selected' : '' }}>Альтернативный (Да/Нет)</option>
                                <option value="text"            {{ old('type', $question->type) == 'text'            ? 'selected' : '' }}>Текстовый ответ (точное совпадение)</option>
                                <option value="completion"      {{ old('type', $question->type) == 'completion'      ? 'selected' : '' }}>Дополнение (вставить слово)</option>
                                <option value="essay"           {{ old('type', $question->type) == 'essay'           ? 'selected' : '' }}>Эссе (проверяется вручную)</option>
                                <option value="matching"        {{ old('type', $question->type) == 'matching'        ? 'selected' : '' }}>Установление соответствия</option>
                                <option value="sequence"        {{ old('type', $question->type) == 'sequence'        ? 'selected' : '' }}>Правильная последовательность</option>
                                <option value="dropdown"        {{ old('type', $question->type) == 'dropdown'        ? 'selected' : '' }}>Выпадающий список</option>
                                <option value="drag_drop"       {{ old('type', $question->type) == 'drag_drop'       ? 'selected' : '' }}>Перетаскивание элементов</option>
                                <option value="table_fill"      {{ old('type', $question->type) == 'table_fill'      ? 'selected' : '' }}>Заполнение таблицы</option>
                                <option value="file_upload"     {{ old('type', $question->type) == 'file_upload'     ? 'selected' : '' }}>Загрузка файла</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Баллы -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Баллы за вопрос</label>
                            <input type="number" name="points" class="form-control" value="{{ old('points', $question->points ?? 1) }}" min="1">
                        </div>

                        <!-- Порядок -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Порядок (необязательно)</label>
                            <input type="number" name="order" class="form-control" value="{{ old('order', $question->order ?? 0) }}">
                        </div>

                        <!-- БЛОК ДЛЯ ПРАВИЛЬНОГО ТЕКСТА (completion, text) -->
                        <div id="blockCorrectText" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Правильный ответ (для дополнения / текста)</label>
                                <input type="text" name="correct_text" class="form-control" value="{{ old('correct_text', $question->correct_text) }}">
                                <div class="form-text">Точное совпадение (регистр не важен)</div>
                            </div>
                        </div>

                        <!-- БЛОК ВАРИАНТОВ ОТВЕТОВ (single_choice, multiple_choice, alternative) -->
                        <div id="blockChoices" style="display: none;">
                            <label class="form-label fw-semibold">Варианты ответов</label>
                            <div id="choicesContainer">
                                @php $choices = old('choices', $question->choices->toArray()); @endphp
                                @foreach($choices as $index => $choice)
                                    <div class="input-group mb-2 choice-item" @if(isset($choice['id'])) data-id="{{ $choice['id'] }}" @endif>
                                        <input type="text" name="choices[{{ $index }}][text]" class="form-control" value="{{ $choice['text'] }}" placeholder="Текст варианта">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="choices[{{ $index }}][is_correct]" value="1" class="form-check-input mt-0" {{ isset($choice['is_correct']) && $choice['is_correct'] ? 'checked' : '' }}>
                                            <label class="form-check-label ms-1">Правильный</label>
                                        </div>
                                        @if(isset($choice['id']))
                                            <input type="hidden" name="choices[{{ $index }}][id]" value="{{ $choice['id'] }}">
                                        @endif
                                        <button type="button" class="btn btn-outline-danger remove-choice">×</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addChoice" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить вариант</button>
                            <input type="hidden" name="deleted_choices" id="deletedChoices" value="">
                        </div>

                        <!-- БЛОК СООТВЕТСТВИЯ (matching) -->
                        <div id="blockMatching" style="display: none;">
                            <label class="form-label fw-semibold">Пары соответствия</label>
                            <div id="pairsContainer">
                                @php $pairs = old('pairs', $question->matchingPairs->toArray()); @endphp
                                @foreach($pairs as $index => $pair)
                                    <div class="row mb-2 pair-item" @if(isset($pair['id'])) data-id="{{ $pair['id'] }}" @endif>
                                        <div class="col-md-5"><input type="text" name="pairs[{{ $index }}][left_text]" class="form-control" value="{{ $pair['left_text'] }}" placeholder="Левый элемент"></div>
                                        <div class="col-md-5"><input type="text" name="pairs[{{ $index }}][right_text]" class="form-control" value="{{ $pair['right_text'] }}" placeholder="Правый элемент"></div>
                                        <div class="col-md-2">
                                            @if(isset($pair['id'])) <input type="hidden" name="pairs[{{ $index }}][id]" value="{{ $pair['id'] }}"> @endif
                                            <button type="button" class="btn btn-outline-danger remove-pair">×</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addPair" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить пару</button>
                            <input type="hidden" name="deleted_pairs" id="deletedPairs" value="">
                        </div>

                        <!-- БЛОК ПОСЛЕДОВАТЕЛЬНОСТИ (sequence) -->
                        <div id="blockSequence" style="display: none;">
                            <label class="form-label fw-semibold">Элементы последовательности (в правильном порядке)</label>
                            <div id="sequenceContainer">
                                @php $items = old('sequence_items', $question->sequenceItems->sortBy('correct_order')->toArray()); @endphp
                                @foreach($items as $index => $item)
                                    <div class="row mb-2 sequence-item" @if(isset($item['id'])) data-id="{{ $item['id'] }}" @endif>
                                        <div class="col-md-10"><input type="text" name="sequence_items[{{ $index }}][item_text]" class="form-control" value="{{ $item['item_text'] }}" placeholder="Элемент"></div>
                                        <div class="col-md-2">
                                            <input type="hidden" name="sequence_items[{{ $index }}][correct_order]" value="{{ $item['correct_order'] ?? $index }}">
                                            @if(isset($item['id'])) <input type="hidden" name="sequence_items[{{ $index }}][id]" value="{{ $item['id'] }}"> @endif
                                            <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addSequence" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить элемент</button>
                            <input type="hidden" name="deleted_sequence_items" id="deletedSequence" value="">
                        </div>

                        <!-- БЛОК ВЫПАДАЮЩЕГО СПИСКА (dropdown) -->
                        <div id="blockDropdown" style="display: none;">
                            <label class="form-label fw-semibold">Опции выпадающего списка</label>
                            <div id="dropdownContainer">
                                @php $options = old('dropdown_options', $question->dropdownOptions->toArray()); @endphp
                                @foreach($options as $index => $opt)
                                    <div class="input-group mb-2 dropdown-item" @if(isset($opt['id'])) data-id="{{ $opt['id'] }}" @endif>
                                        <input type="text" name="dropdown_options[{{ $index }}][option_text]" class="form-control" value="{{ $opt['option_text'] }}" placeholder="Текст опции">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="dropdown_options[{{ $index }}][is_correct]" value="1" class="form-check-input mt-0" {{ isset($opt['is_correct']) && $opt['is_correct'] ? 'checked' : '' }}>
                                            <label class="form-check-label ms-1">Правильная</label>
                                        </div>
                                        @if(isset($opt['id'])) <input type="hidden" name="dropdown_options[{{ $index }}][id]" value="{{ $opt['id'] }}"> @endif
                                        <button type="button" class="btn btn-outline-danger remove-dropdown">×</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addDropdown" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить опцию</button>
                            <input type="hidden" name="deleted_dropdown_options" id="deletedDropdown" value="">
                        </div>

                        <!-- БЛОК DRAG & DROP -->
                        <div id="blockDragDrop" style="display: none;">
                            <label class="form-label fw-semibold">Элементы для перетаскивания</label>
                            <div id="dragContainer">
                                @php $drags = old('drag_items', $question->dragDropItems->toArray()); @endphp
                                @foreach($drags as $index => $drag)
                                    <div class="row mb-2 drag-item" @if(isset($drag['id'])) data-id="{{ $drag['id'] }}" @endif>
                                        <div class="col-md-4"><input type="text" name="drag_items[{{ $index }}][item_text]" class="form-control" value="{{ $drag['item_text'] }}" placeholder="Элемент"></div>
                                        <div class="col-md-3"><input type="text" name="drag_items[{{ $index }}][target_zone]" class="form-control" value="{{ $drag['target_zone'] }}" placeholder="Целевая зона"></div>
                                        <div class="col-md-3"><input type="text" name="drag_items[{{ $index }}][correct_zone]" class="form-control" value="{{ $drag['correct_zone'] }}" placeholder="Правильная зона"></div>
                                        <div class="col-md-2">
                                            @if(isset($drag['id'])) <input type="hidden" name="drag_items[{{ $index }}][id]" value="{{ $drag['id'] }}"> @endif
                                            <button type="button" class="btn btn-outline-danger remove-drag">×</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addDrag" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить элемент</button>
                            <input type="hidden" name="deleted_drag_items" id="deletedDrag" value="">
                        </div>

                        <!-- БЛОК ЗАПОЛНЕНИЯ ТАБЛИЦЫ (table_fill) -->
                        <div id="blockTableFill" style="display: none;">
                            @php
                                $tableFill = $question->tableFill;
                                $headers = old('table_headers', $tableFill ? json_decode($tableFill->headers, true) : []);
                                $rows = old('table_rows', $tableFill ? json_decode($tableFill->rows, true) : []);
                                $cells = old('table_cells', $tableFill ? $tableFill->cells->toArray() : []);
                            @endphp
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Заголовки столбцов (через запятую)</label>
                                <input type="text" name="table_headers" class="form-control" value="{{ is_array($headers) ? implode(',', $headers) : $headers }}" placeholder="Столбец 1, Столбец 2, ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Названия строк (через запятую)</label>
                                <input type="text" name="table_rows" class="form-control" value="{{ is_array($rows) ? implode(',', $rows) : $rows }}" placeholder="Строка 1, Строка 2, ...">
                            </div>
                            <label class="form-label fw-semibold">Ячейки (правильные ответы)</label>
                            <div id="tableCellsContainer">
                                @foreach($cells as $index => $cell)
                                    <div class="row mb-2 table-cell-item" @if(isset($cell['id'])) data-id="{{ $cell['id'] }}" @endif>
                                        <div class="col-md-3"><input type="number" name="table_cells[{{ $index }}][row]" class="form-control" value="{{ $cell['row_index'] }}" placeholder="Строка (0)"></div>
                                        <div class="col-md-3"><input type="number" name="table_cells[{{ $index }}][col]" class="form-control" value="{{ $cell['col_index'] }}" placeholder="Столбец (0)"></div>
                                        <div class="col-md-4"><input type="text" name="table_cells[{{ $index }}][expected_answer]" class="form-control" value="{{ $cell['expected_answer'] }}" placeholder="Ожидаемый ответ"></div>
                                        <div class="col-md-2">
                                            @if(isset($cell['id'])) <input type="hidden" name="table_cells[{{ $index }}][id]" value="{{ $cell['id'] }}"> @endif
                                            <button type="button" class="btn btn-outline-danger remove-cell">×</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addCell" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> Добавить ячейку</button>
                            <input type="hidden" name="deleted_table_cells" id="deletedCells" value="">
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
        // ---------- Переключение блоков в зависимости от типа ----------
        const typeSelect = document.getElementById('type');
        const blocks = {
            correctText: document.getElementById('blockCorrectText'),
            choices: document.getElementById('blockChoices'),
            matching: document.getElementById('blockMatching'),
            sequence: document.getElementById('blockSequence'),
            dropdown: document.getElementById('blockDropdown'),
            dragDrop: document.getElementById('blockDragDrop'),
            tableFill: document.getElementById('blockTableFill')
        };

        function hideAllBlocks() {
            for (let k in blocks) {
                if (blocks[k]) blocks[k].style.display = 'none';
            }
        }

        function showBlockForType(type) {
            hideAllBlocks();
            if (type === 'text' || type === 'completion' || type === 'essay') {
                if (blocks.correctText) blocks.correctText.style.display = 'block';
                if (type === 'completion' || type === 'text') {
                    // оставляем correct_text видимым
                }
            } else if (type === 'single_choice' || type === 'multiple_choice' || type === 'alternative') {
                if (blocks.choices) blocks.choices.style.display = 'block';
            } else if (type === 'matching') {
                if (blocks.matching) blocks.matching.style.display = 'block';
            } else if (type === 'sequence') {
                if (blocks.sequence) blocks.sequence.style.display = 'block';
            } else if (type === 'dropdown') {
                if (blocks.dropdown) blocks.dropdown.style.display = 'block';
            } else if (type === 'drag_drop') {
                if (blocks.dragDrop) blocks.dragDrop.style.display = 'block';
            } else if (type === 'table_fill') {
                if (blocks.tableFill) blocks.tableFill.style.display = 'block';
            }
        }

        typeSelect.addEventListener('change', function() { showBlockForType(this.value); });
        showBlockForType(typeSelect.value);

        // ---------- ОБЩАЯ ФУНКЦИЯ ДЛЯ УДАЛЕНИЯ (обновление hidden-полей) ----------
        function setupDeletion(containerId, deletedInputId, itemClass, idAttr) {
            const container = document.getElementById(containerId);
            const deletedInput = document.getElementById(deletedInputId);
            let deletedIds = [];

            if (!container) return;

            container.addEventListener('click', function(e) {
                const btn = e.target.closest('.' + itemClass);
                if (!btn) return;
                const item = btn.closest('.' + itemClass.replace('remove-', ''));
                if (!item) return;
                const id = item.getAttribute('data-id');
                if (id && id !== '') {
                    deletedIds.push(id);
                    deletedInput.value = deletedIds.join(',');
                }
                item.remove();
                // Перенумерация индексов
                reindexItems(containerId, itemClass.replace('remove-', ''));
            });
        }

        function reindexItems(containerId, itemClass) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const items = container.querySelectorAll('.' + itemClass);
            items.forEach((item, idx) => {
                // Обновляем name для всех полей внутри
                item.querySelectorAll('input, select, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        let newName = name.replace(/\[\d+\]/, '[' + idx + ']');
                        input.setAttribute('name', newName);
                    }
                });
            });
        }

        // Настраиваем удаление для каждого типа
        setupDeletion('choicesContainer', 'deletedChoices', 'remove-choice', 'choice-item');
        setupDeletion('pairsContainer', 'deletedPairs', 'remove-pair', 'pair-item');
        setupDeletion('sequenceContainer', 'deletedSequence', 'remove-sequence', 'sequence-item');
        setupDeletion('dropdownContainer', 'deletedDropdown', 'remove-dropdown', 'dropdown-item');
        setupDeletion('dragContainer', 'deletedDrag', 'remove-drag', 'drag-item');
        setupDeletion('tableCellsContainer', 'deletedCells', 'remove-cell', 'table-cell-item');

        // ---------- ДОБАВЛЕНИЕ НОВЫХ ЭЛЕМЕНТОВ ----------
        // Варианты ответов
        document.getElementById('addChoice')?.addEventListener('click', function() {
            const container = document.getElementById('choicesContainer');
            const index = container.children.length;
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
            container.appendChild(newItem);
        });

        // Пары соответствия
        document.getElementById('addPair')?.addEventListener('click', function() {
            const container = document.getElementById('pairsContainer');
            const index = container.children.length;
            const newItem = document.createElement('div');
            newItem.className = 'row mb-2 pair-item';
            newItem.innerHTML = `
                <div class="col-md-5"><input type="text" name="pairs[${index}][left_text]" class="form-control" placeholder="Левый элемент"></div>
                <div class="col-md-5"><input type="text" name="pairs[${index}][right_text]" class="form-control" placeholder="Правый элемент"></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger remove-pair">×</button></div>
            `;
            container.appendChild(newItem);
        });

        // Элементы последовательности
        document.getElementById('addSequence')?.addEventListener('click', function() {
            const container = document.getElementById('sequenceContainer');
            const index = container.children.length;
            const newItem = document.createElement('div');
            newItem.className = 'row mb-2 sequence-item';
            newItem.innerHTML = `
                <div class="col-md-10"><input type="text" name="sequence_items[${index}][item_text]" class="form-control" placeholder="Элемент"></div>
                <div class="col-md-2">
                    <input type="hidden" name="sequence_items[${index}][correct_order]" value="${index}">
                    <button type="button" class="btn btn-outline-danger remove-sequence">×</button>
                </div>
            `;
            container.appendChild(newItem);
        });

        // Выпадающий список
        document.getElementById('addDropdown')?.addEventListener('click', function() {
            const container = document.getElementById('dropdownContainer');
            const index = container.children.length;
            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2 dropdown-item';
            newItem.innerHTML = `
                <input type="text" name="dropdown_options[${index}][option_text]" class="form-control" placeholder="Текст опции">
                <div class="input-group-text">
                    <input type="checkbox" name="dropdown_options[${index}][is_correct]" value="1" class="form-check-input mt-0">
                    <label class="form-check-label ms-1">Правильная</label>
                </div>
                <button type="button" class="btn btn-outline-danger remove-dropdown">×</button>
            `;
            container.appendChild(newItem);
        });

        // Drag & Drop
        document.getElementById('addDrag')?.addEventListener('click', function() {
            const container = document.getElementById('dragContainer');
            const index = container.children.length;
            const newItem = document.createElement('div');
            newItem.className = 'row mb-2 drag-item';
            newItem.innerHTML = `
                <div class="col-md-4"><input type="text" name="drag_items[${index}][item_text]" class="form-control" placeholder="Элемент"></div>
                <div class="col-md-3"><input type="text" name="drag_items[${index}][target_zone]" class="form-control" placeholder="Целевая зона"></div>
                <div class="col-md-3"><input type="text" name="drag_items[${index}][correct_zone]" class="form-control" placeholder="Правильная зона"></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger remove-drag">×</button></div>
            `;
            container.appendChild(newItem);
        });

        // Ячейки таблицы
        document.getElementById('addCell')?.addEventListener('click', function() {
            const container = document.getElementById('tableCellsContainer');
            const index = container.children.length;
            const newItem = document.createElement('div');
            newItem.className = 'row mb-2 table-cell-item';
            newItem.innerHTML = `
                <div class="col-md-3"><input type="number" name="table_cells[${index}][row]" class="form-control" placeholder="Строка (0)"></div>
                <div class="col-md-3"><input type="number" name="table_cells[${index}][col]" class="form-control" placeholder="Столбец (0)"></div>
                <div class="col-md-4"><input type="text" name="table_cells[${index}][expected_answer]" class="form-control" placeholder="Ожидаемый ответ"></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger remove-cell">×</button></div>
            `;
            container.appendChild(newItem);
        });

        // Дополнительно: при изменении типа сбрасываем deleted-поля (опционально)
        typeSelect.addEventListener('change', function() {
            document.getElementById('deletedChoices')?.setAttribute('value', '');
            document.getElementById('deletedPairs')?.setAttribute('value', '');
            document.getElementById('deletedSequence')?.setAttribute('value', '');
            document.getElementById('deletedDropdown')?.setAttribute('value', '');
            document.getElementById('deletedDrag')?.setAttribute('value', '');
            document.getElementById('deletedCells')?.setAttribute('value', '');
        });
    });
</script>
@endpush
@endsection