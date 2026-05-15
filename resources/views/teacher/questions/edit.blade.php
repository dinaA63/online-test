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
                            </select>
                        </div>

                        <div id="choicesBlock" style="display: {{ $question->type != 'text' ? 'block' : 'none' }};">
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
        let deletedIds = [];

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