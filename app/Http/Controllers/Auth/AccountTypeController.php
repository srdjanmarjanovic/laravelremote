<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AccountTypeController extends Controller
{
    /**
     * Show the account type selection page.
     */
    public function show(): Response
    {
        return Inertia::render('auth/SelectRole');
    }

    /**
     * Store the selected account type.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'string', Rule::in(['developer', 'hr'])],
        ]);

        $user = $request->user();
        $role = $request->input('role');

        $user->update([
            'role' => $role,
        ]);

        // HR users need to set up their company profile first
        if ($role === 'hr') {
            return redirect()->route('hr.company.setup');
        }

        return redirect()->route('dashboard');
    }
}
