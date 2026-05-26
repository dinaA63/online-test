<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Avatar
{
    public static function url(?User $user): ?string
    {
        if (!$user?->avatar) {
            return null;
        }

        if (!Storage::disk('public')->exists($user->avatar)) {
            return null;
        }

        return asset('storage/' . ltrim($user->avatar, '/'));
    }

    public static function initials(?User $user): string
    {
        if (!$user?->name) {
            return '?';
        }

        $parts = preg_split('/\s+/u', trim($user->name)) ?: [];

        return mb_strtoupper(
            mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? $parts[0] ?? '', 0, 1)
        );
    }
}
