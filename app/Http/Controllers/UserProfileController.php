<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    /**
     * Показывает профиль текущего пользователя.
     */
    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    /**
     * Обновляет аватар и описание.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'bio'    => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Обработка аватара
        if ($request->hasFile('avatar')) {
            // Удаляем старый аватар, если есть
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->bio = $request->bio;
        $user->save();

        return back()->with('success', 'Профиль успешно обновлён.');
    }
}