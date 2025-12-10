<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // If user hasn't selected a role yet, redirect to selection page
        if (! $request->user()->role) {
            return redirect()->route('account-type.show');
        }

        foreach ($roles as $role) {
            if ($request->user()->role === $role) {
                return $next($request);
            }
        }

        // For Inertia requests, redirect to dashboard instead of showing error overlay
        if ($request->header('X-Inertia')) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }

        // For non-Inertia requests (API, etc.), use abort
        abort(403, 'Unauthorized action.');
    }
}
