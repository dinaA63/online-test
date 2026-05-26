@extends('layouts.app')
@section('title', 'Импорт вопросов из GIFT')

@section('content')
<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="page-hero mb-4">
                <h1 class="page-title"><i class="fas fa-file-import me-2"></i>Импорт GIFT</h1>
                <p class="page-subtitle mb-0">Загрузите файл в формате Moodle GIFT (.txt) или используйте пример <code>gift.txt</code> из проекта.</p>
            </div>

            <div class="card card-premium">
                <div class="card-body p-4">
                    <form action="{{ route('teacher.gift.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="test_id" class="form-label">Тест</label>
                            <select name="test_id" id="test_id" class="form-select @error('test_id') is-invalid @enderror" required>
                                <option value="">— Выберите тест —</option>
                                @foreach($tests as $test)
                                    <option value="{{ $test->id }}" {{ (string) old('test_id', $selectedTestId) === (string) $test->id ? 'selected' : '' }}>
                                        {{ $test->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('test_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="file" class="form-label">Файл GIFT</label>
                            <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".txt,.gift">
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Поддерживаются: одиночный/множественный выбор, соответствие, открытые ответы (~~~). До 5 МБ.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Импортировать файл
                            </button>

                            @if($sampleAvailable)
                                <button type="submit" name="use_sample" value="1" class="btn btn-outline-primary" formaction="{{ route('teacher.gift.import') }}">
                                    <i class="fas fa-bolt me-2"></i>Импортировать gift.txt из проекта
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-premium mt-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-2"><i class="fas fa-circle-info me-2"></i>Формат GIFT</h6>
                    <pre class="gift-example mb-0">::Название::Текст вопроса
{
   =правильный ответ
   ~неправильный
}

::Соответствие::Установите соответствие...
{
   =[1] -> A. Вариант A
   =[2] -> B. Вариант B
}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
