@extends('layouts.app')
@section('title', 'Мои тесты')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6"><i class="fas fa-chalkboard-teacher me-2"></i>Мои тесты</h1>
        <a href="{{ route('teacher.tests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Создать тест
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @forelse($tests as $test)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-file-alt me-2"></i>{{ $test->title }}
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted">{{ Str::limit($test->description, 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="badge bg-info me-1">Вопросов: {{ $test->questions->count() }}</span>
                                <span class="badge bg-secondary">Попыток: {{ $test->max_attempts }}</span>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('teacher.tests.edit', $test) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $test->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модальное окно удаления -->
            <div class="modal fade" id="deleteModal{{ $test->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Подтверждение удаления</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Вы уверены, что хотите удалить тест <strong>{{ $test->title }}</strong>?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <form action="{{ route('teacher.tests.destroy', $test) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>У вас пока нет созданных тестов.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection