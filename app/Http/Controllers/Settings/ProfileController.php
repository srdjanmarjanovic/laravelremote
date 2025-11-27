<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'isSocialUser' => $request->user()->isSocialUser(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Social users confirm with email, regular users with password
        if ($user->isSocialUser()) {
            $request->validate([
                'email' => ['required', 'email', function ($attribute, $value, $fail) use ($user) {
                    if ($value !== $user->email) {
                        $fail('The provided email does not match your account email.');
                    }
                }],
            ]);
        } else {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);
        }

        Auth::logout();

        // Anonymize user data
        $randomId = Str::random(8);
        $user->forceFill([
            'name' => "Deleted User {$randomId}",
            'email' => "deleted.{$randomId}@deleted.local",
            'password' => bcrypt(Str::random(32)),
            'email_verified_at' => null,
            'remember_token' => null,
        ]);

        // Delete developer profile and CV if exists
        if ($user->developerProfile) {
            // Delete CV file if exists
            if ($user->developerProfile->cv_path) {
                Storage::disk('private')->delete($user->developerProfile->cv_path);
            }

            // Delete profile photo if exists
            if ($user->developerProfile->photo_path) {
                Storage::disk('public')->delete($user->developerProfile->photo_path);
            }

            $user->developerProfile->delete();
        }

        // Delete social accounts
        $user->socialAccounts()->delete();

        // Save anonymized data and soft delete
        $user->save();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
