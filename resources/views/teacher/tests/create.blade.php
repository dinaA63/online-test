@extends('layouts.app')
@section('title', 'Создать тест')

@section('content')
<div class="container py-4">
    <x-page-header title="Новый тест" label="Создание" />
    <div class="stone-card mx-auto" style="max-width: 640px;">
        <form action="{{ route('teacher.tests.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label fw-semibold">Название *</label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Описание</label>
                <textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="group_id" class="form-label fw-semibold">Группа студентов</label>
                <select name="group_id" id="group_id" class="form-select @error('group_id') is-invalid @enderror">
                    <option value="">Все студенты</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Если выбрана группа — тест увидят только её студенты</small>
                @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="time_limit" class="form-label fw-semibold">Время (мин)</label>
                    <input type="number" name="time_limit" id="time_limit" class="form-control" value="{{ old('time_limit', 0) }}" min="0">
                    <small class="text-muted">0 — без лимита</small>
                </div>
                <div class="col-md-6">
                    <label for="max_attempts" class="form-label fw-semibold">Попыток</label>
                    <input type="number" name="max_attempts" id="max_attempts" class="form-control" value="{{ old('max_attempts', 1) }}" min="1">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-pill">Создать</button>
                <a href="{{ route('teacher.tests.index') }}" class="btn btn-ghost btn-pill">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
