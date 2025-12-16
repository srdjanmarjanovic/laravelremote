<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\DeveloperProfile;
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
        $user = $request->user();
        $data = [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'isSocialUser' => $user->isSocialUser(),
        ];

        // Load developer profile if user is a developer
        if ($user->isDeveloper()) {
            $data['developerProfile'] = $user->developerProfile ?? new DeveloperProfile;
        }

        return Inertia::render('settings/Profile', $data);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Update user profile (name, email)
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Handle developer profile update if user is a developer
        if ($user->isDeveloper() && isset($validated['developer_profile'])) {
            $developerData = $validated['developer_profile'];
            $profile = $user->developerProfile ?? new DeveloperProfile(['user_id' => $user->id]);

            // Handle CV upload
            if ($request->hasFile('developer_profile.cv')) {
                // Delete old CV if exists
                if ($profile->cv_path && Storage::disk('private')->exists($profile->cv_path)) {
                    Storage::disk('private')->delete($profile->cv_path);
                }

                $cvPath = $request->file('developer_profile.cv')->store('cvs', 'private');
                $developerData['cv_path'] = $cvPath;
            }

            // Handle profile photo upload
            if ($request->hasFile('developer_profile.profile_photo')) {
                // Delete old photo if exists
                if ($profile->profile_photo_path && Storage::disk('public')->exists($profile->profile_photo_path)) {
                    Storage::disk('public')->delete($profile->profile_photo_path);
                }

                $photoPath = $request->file('developer_profile.profile_photo')->store('profile-photos', 'public');
                $developerData['profile_photo_path'] = $photoPath;
            }

            // Remove file inputs from data
            unset($developerData['cv'], $developerData['profile_photo']);

            if ($profile->exists) {
                $profile->update($developerData);
            } else {
                $user->developerProfile()->create($developerData);
            }
        }

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
