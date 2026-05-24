<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Отображает список пользователей с фильтрацией.
     */
    public function index(Request $request)
    {
        $query = User::with('groups');

        // Фильтр по роли
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Фильтр по группе
        if ($request->filled('group_id')) {
            $query->whereHas('groups', function ($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        $users = $query->paginate(20);
        $groups = Group::all();

        return view('admin.users.index', compact('users', 'groups'));
    }

    /**
     * Показывает форму редактирования пользователя.
     */
    public function edit(User $user)
    {
        $groups = Group::all();
        return view('admin.users.edit', compact('user', 'groups'));
    }

    /**
     * Обновляет данные пользователя и его группы.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'role'      => 'required|in:student,teacher,admin',
            'group_ids'   => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
        ]);

        $user->update($validated);

        // Синхронизация групп
        $user->groups()->sync($validated['group_ids'] ?? []);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Пользователь успешно обновлён.');
    }

    /**
     * Удаляет пользователя.
     */
    public function destroy(User $user)
    {
        // Защита от удаления самого себя (опционально)
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                             ->with('error', 'Вы не можете удалить сами себя.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Пользователь удалён.');
    }
}