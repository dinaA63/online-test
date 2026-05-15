<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (auth()->user()->role !== $role) {
            abort(403, 'У вас нет доступа.');
        }
        return $next($request);
    }
}