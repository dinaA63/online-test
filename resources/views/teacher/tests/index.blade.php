@extends('layouts.app')
@section('title', 'Мои тесты')

@section('content')
<div class="container py-4">
    <x-page-header title="Мои тесты" label="Преподаватель">
        <x-slot:actions>
            <a href="{{ route('teacher.gift.import.create') }}" class="btn btn-ghost btn-pill"><i class="fas fa-file-import me-1"></i>GIFT</a>
            <a href="{{ route('teacher.tests.create') }}" class="btn btn-primary btn-pill"><i class="fas fa-plus me-1"></i>Создать тест</a>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4">
        @forelse($tests as $test)
            <div class="col-md-6 col-lg-4">
                <div class="tile-card">
                    <div class="tile-card-header">{{ $test->title }}</div>
                    <div class="tile-card-body">
                        <p class="mb-3">{{ Str::limit($test->description, 100) ?: 'Без описания' }}</p>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge-soft badge-soft-info">Вопросов: {{ $test->questions_count }}</span>
                            <span class="badge-soft badge-soft-muted">Попыток: {{ $test->max_attempts }}</span>
                            @if($test->pending_reviews_count > 0)
                                <span class="badge-soft badge-soft-warning">На проверке: {{ $test->pending_reviews_count }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="tile-card-footer d-flex gap-2">
                        <a href="{{ route('teacher.tests.show', $test) }}" class="btn btn-ghost btn-sm btn-pill flex-grow-1"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('teacher.tests.edit', $test) }}" class="btn btn-ghost btn-sm btn-pill flex-grow-1"><i class="fas fa-edit"></i></a>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-pill" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $test->id }}"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="deleteModal{{ $test->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Удалить тест?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">Тест <strong>{{ $test->title }}</strong> будет удалён безвозвратно.</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-ghost btn-pill" data-bs-dismiss="modal">Отмена</button>
                            <form action="{{ route('teacher.tests.destroy', $test) }}" method="POST">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-pill">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-folder-open fa-2x mb-3 d-block" style="color: var(--primary);"></i>
                    <p class="mb-3">У вас пока нет тестов</p>
                    <a href="{{ route('teacher.tests.create') }}" class="btn btn-primary btn-pill">Создать первый тест</a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
