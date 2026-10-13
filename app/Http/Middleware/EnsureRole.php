<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        abort_unless($user && collect($roles)->contains(fn (string $role): bool => $user->hasRole($role)), 403);
        abort_if($user->status !== 'active', 403);

        return $next($request);
    }
}
