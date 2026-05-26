@extends('layouts.app')
@section('title', 'Импорт GIFT')

@section('content')
<div class="container py-4">
    <x-page-header title="Импорт GIFT" label="Преподаватель" />
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="stone-card mb-4">
                <p class="text-muted mb-4">Загрузите файл Moodle GIFT (.txt).</p>
                <form action="{{ route('teacher.gift.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="test_id" class="form-label fw-semibold">Тест</label>
                        <select name="test_id" id="test_id" class="form-select @error('test_id') is-invalid @enderror" required>
                            <option value="">— Выберите —</option>
                            @foreach($tests as $test)
                                <option value="{{ $test->id }}" {{ (string) old('test_id', $selectedTestId) === (string) $test->id ? 'selected' : '' }}>{{ $test->title }}</option>
                            @endforeach
                        </select>
                        @error('test_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="file" class="form-label fw-semibold">Файл</label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".txt,.gift">
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Выбор, соответствие, последовательность, текст. До 5 МБ.</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-pill w-100"><i class="fas fa-upload me-2"></i>Импортировать</button>
                </form>
            </div>
            <div class="stone-card">
                <h2 class="h6 fw-bold mb-2">Подсказка по формату</h2>
                <ul class="text-muted small mb-0 ps-3">
                    <li>Одиночный выбор: <code>=верный</code></li>
                    <li>Множественный: <code>~%50%частично</code></li>
                    <li>Соответствие: пары через <code>=</code></li>
                    <li>Текст: <code>~~~</code> или essay</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
