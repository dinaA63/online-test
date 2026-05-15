@extends('layouts.app')
@section('title', 'Создать тест')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2" style="color: var(--primary);"></i>Создание нового теста</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.tests.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Название теста <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Описание</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="time_limit" class="form-label fw-semibold">Время на тест (минуты)</label>
                                <input type="number" name="time_limit" id="time_limit" class="form-control @error('time_limit') is-invalid @enderror" value="{{ old('time_limit', 0) }}" min="0">
                                <small class="text-muted">0 – без ограничения</small>
                                @error('time_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_attempts" class="form-label fw-semibold">Максимум попыток</label>
                                <input type="number" name="max_attempts" id="max_attempts" class="form-control @error('max_attempts') is-invalid @enderror" value="{{ old('max_attempts', 1) }}" min="1">
                                @error('max_attempts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('teacher.tests.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Создать тест</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection