<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Only check for developers
        if (! $user->isDeveloper()) {
            return $next($request);
        }

        // Check if developer has complete profile
        if (! $user->hasCompleteProfile()) {
            return redirect()->route('developer.profile.edit')
                ->with('warning', 'Please complete your profile before applying to positions.');
        }

        return $next($request);
    }
}
