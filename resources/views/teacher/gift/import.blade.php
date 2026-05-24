@extends('layouts.app')

@section('title', 'Импорт вопросов из GIFT')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h4><i class="fas fa-file-import me-2"></i>Импорт GIFT</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.gift.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="test_id" class="form-label fw-semibold">Тест</label>
                            <select name="test_id" id="test_id" class="form-select @error('test_id') is-invalid @enderror" required>
                                <option value="">-- Выберите тест --</option>
                                @foreach(auth()->user()->tests as $test)
                                    <option value="{{ $test->id }}" {{ old('test_id') == $test->id ? 'selected' : '' }}>{{ $test->title }}</option>
                                @endforeach
                            </select>
                            @error('test_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label fw-semibold">Файл GIFT (.txt)</label>
                            <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".txt,.gift" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Файл в формате GIFT (обычно .txt). Максимальный размер 2 МБ.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload me-2"></i>Импортировать</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection