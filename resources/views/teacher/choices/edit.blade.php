@extends('layouts.app')
@section('title', 'Редактирование варианта')

@section('content')
<div class="container py-4">
    <x-page-header title="Вариант ответа" label="Редактирование" />
    <div class="stone-card" style="max-width: 560px;">
        <form action="{{ route('teacher.choices.update', $choice) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Текст</label>
                <input type="text" name="text" class="form-control" value="{{ old('text', $choice->text) }}" required>
            </div>
            <div class="mb-4 form-check">
                <input type="hidden" name="is_correct" value="0">
                <input type="checkbox" name="is_correct" value="1" class="form-check-input" id="is_correct" {{ old('is_correct', $choice->is_correct) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_correct">Правильный ответ</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-pill">Сохранить</button>
                <a href="{{ route('teacher.questions.edit', $choice->question) }}" class="btn btn-ghost btn-pill">Назад</a>
            </div>
        </form>
    </div>
</div>
@endsection
