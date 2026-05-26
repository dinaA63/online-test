@extends('layouts.app')

@section('title', 'Управление пользователями')

@section('content')
<div class="container py-4">
    <x-page-header title="Пользователи" label="Администрирование" />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Форма фильтрации -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-auto">
            <select name="role" class="form-select">
                <option value="">Все роли</option>
                <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Студент</option>
                <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Преподаватель</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Администратор</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="group_id" class="form-select">
                <option value="">Все группы</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Фильтровать</button>
        </div>
    </form>

    <div class="stone-card">
    <div class="table-responsive">
        <table class="table table-minimal align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Группы</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'teacher' ? 'primary' : 'success') }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>{{ $user->groups->pluck('name')->join(', ') ?: '—' }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Редактировать">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Пользователи не найдены.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->appends(request()->query())->links() }}
    </div>
</div>
@endsection