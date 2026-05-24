<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Отображает список всех групп.
     */
    public function index()
    {
        $groups = Group::with('users')->get();
        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Показывает форму создания новой группы.
     */
    public function create()
    {
        return view('admin.groups.create');
    }

    /**
     * Сохраняет новую группу в базе данных.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Group::create($validated);

        return redirect()
            ->route('admin.groups.index')
            ->with('success', 'Группа успешно создана.');
    }

    /**
     * Показывает форму редактирования группы.
     */
    public function edit(Group $group)
    {
        $users = User::all();
        return view('admin.groups.edit', compact('group', 'users'));
    }

    /**
     * Обновляет данные группы и состав её участников.
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids'    => 'nullable|array',
        ]);

        $group->update($validated);
        $group->users()->sync($validated['user_ids'] ?? []);

        return redirect()
            ->route('admin.groups.index')
            ->with('success', 'Группа обновлена.');
    }

    /**
     * Удаляет группу.
     */
    public function destroy(Group $group)
    {
        $group->delete();

        return redirect()
            ->route('admin.groups.index')
            ->with('success', 'Группа удалена.');
    }
}