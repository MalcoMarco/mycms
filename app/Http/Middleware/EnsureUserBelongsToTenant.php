<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTenant
{
    /**
     * Middleware to ensure that the authenticated user belongs to the current tenant.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $tenant = tenant(); // helper de stancl

        if (! $tenant) {
            abort(404);
        }

        $belongs = $tenant->users()
            ->where('user_id', $user->id)
            ->exists();

        if (! $belongs) {
            abort(403, 'No tienes acceso a este tenant.');
        }

        return $next($request);
    }
}
