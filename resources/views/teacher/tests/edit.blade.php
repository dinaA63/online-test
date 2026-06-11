@extends('layouts.app')
@section('title', 'Редактировать тест')

@section('content')
<div class="container py-4">
    <x-page-header title="Редактирование теста" :label="$test->title" />
    <div class="stone-card mx-auto" style="max-width: 640px;">
        <form action="{{ route('teacher.tests.update', $test) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label for="title" class="form-label fw-semibold">Название *</label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $test->title) }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Описание</label>
                <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $test->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label for="group_id" class="form-label fw-semibold">Группа студентов</label>
                <select name="group_id" id="group_id" class="form-select">
                    <option value="">Все студенты</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id', $test->group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="time_limit" class="form-label fw-semibold">Время (мин)</label>
                    <input type="number" name="time_limit" id="time_limit" class="form-control" value="{{ old('time_limit', $test->time_limit) }}" min="0">
                </div>
                <div class="col-md-6">
                    <label for="max_attempts" class="form-label fw-semibold">Попыток</label>
                    <input type="number" name="max_attempts" id="max_attempts" class="form-control" value="{{ old('max_attempts', $test->max_attempts) }}" min="1">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-pill">Сохранить</button>
                <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-ghost btn-pill">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
