<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyProfileComplete
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

        // Only check for HR users
        if (! $user->isHR()) {
            return $next($request);
        }

        // Check if HR has a complete company profile
        if (! $user->hasCompleteCompanyProfile()) {
            return redirect()->route('hr.company.setup')
                ->with('warning', 'Please complete your company profile before posting positions.');
        }

        return $next($request);
    }
}
