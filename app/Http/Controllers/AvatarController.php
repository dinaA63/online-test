<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AvatarController extends Controller
{
    public function show(string $filename): Response
    {
        $filename = basename($filename);

        if (!preg_match('/^[\w.\-]+\.(jpe?g|png|gif|webp)$/i', $filename)) {
            abort(404);
        }

        $path = 'avatars/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
