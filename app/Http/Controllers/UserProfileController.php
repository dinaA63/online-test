<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'bio'    => 'nullable|string|max:500',
            'avatar' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            if (!$file->isValid()) {
                return back()->with('error', 'Не удалось загрузить файл. Попробуйте другой формат.');
            }

            try {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                Storage::disk('public')->makeDirectory('avatars');

                $ext = strtolower($file->extension() ?: $file->guessExtension() ?: 'jpg');
                $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
                $filename = $user->id . '_' . time() . '.' . $ext;
                $path = $file->storeAs('avatars', $filename, 'public');

                if (!$path || !Storage::disk('public')->exists($path)) {
                    throw new \RuntimeException('Файл не сохранён на диск');
                }

                $user->avatar = $path;
                Log::info('Avatar uploaded', ['user_id' => $user->id, 'path' => $path]);
            } catch (\Throwable $e) {
                Log::error('Avatar upload failed', ['error' => $e->getMessage()]);

                return back()->with('error', 'Ошибка при загрузке аватара: ' . $e->getMessage());
            }
        }

        $user->bio = $request->bio;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Профиль успешно обновлён.');
    }
}
