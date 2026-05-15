@extends('layouts.app')
@section('title', 'Редактировать группу')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h4 class="mb-0"><i class="fas fa-edit me-2" style="color: var(--primary);"></i>Редактирование группы "{{ $group->name }}"</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.groups.update', $group) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Название группы</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $group->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Описание</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $group->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Участники группы</label>
                            <select name="user_ids[]" multiple class="form-select" size="8">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                        @selected($group->users->contains($user->id))>
                                        {{ $user->name }} ({{ $user->email }}) - {{ $user->role }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Удерживайте Ctrl (Cmd) для множественного выбора.</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection