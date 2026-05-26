<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        if ($user->avatar && !Storage::disk('public')->exists($user->avatar)) {
            $user->update(['avatar' => null]);
            $user->refresh();
        }

        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'bio'    => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Обработка аватара
        if ($request->hasFile('avatar')) {
            try {
                // Удаляем старый аватар
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Создаём папку avatars, если её нет
                if (!Storage::disk('public')->exists('avatars')) {
                    Storage::disk('public')->makeDirectory('avatars');
                }

                // Сохраняем файл
                $file = $request->file('avatar');
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = $user->id . '_' . time() . '.' . preg_replace('/[^a-z0-9]/', '', $ext);
                $path = $file->storeAs('avatars', $filename, 'public');
                
                $user->avatar = $path;
                
                Log::info('Avatar uploaded', ['user_id' => $user->id, 'path' => $path]);
            } catch (\Exception $e) {
                Log::error('Avatar upload failed', ['error' => $e->getMessage()]);
                return back()->with('error', 'Ошибка при загрузке аватара: ' . $e->getMessage());
            }
        }

        $user->bio = $request->bio;
        $user->save();

        return back()->with('success', 'Профиль успешно обновлён.');
    }
}