@extends('layouts.app')
@section('title', 'Управление группами')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6"><i class="fas fa-users me-2" style="color: var(--primary);"></i>Группы пользователей</h1>
        <a href="{{ route('admin.groups.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Создать группу</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @forelse($groups as $group)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">{{ $group->name }}</h5>
                        <p class="card-text text-muted">{{ $group->description ?: 'Описание отсутствует' }}</p>
                        <p class="card-text"><small class="text-muted">Участников: {{ $group->users->count() }}</small></p>
                    </div>
                    <div class="card-footer bg-transparent border-0 d-flex justify-content-end gap-2 pb-3">
                        <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $group->id }}">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </div>
                </div>
            </div>

            <!-- Модальное окно удаления -->
            <div class="modal fade" id="deleteModal{{ $group->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Подтверждение удаления</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Вы действительно хотите удалить группу <strong>{{ $group->name }}</strong>?
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <form action="{{ route('admin.groups.destroy', $group) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center rounded-4">Группы пока не созданы.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection