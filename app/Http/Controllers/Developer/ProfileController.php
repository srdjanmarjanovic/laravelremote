<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeveloperProfileUpdateRequest;
use App\Models\DeveloperProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the developer profile.
     * Redirects to settings/profile for unified profile management.
     */
    public function edit(): RedirectResponse
    {
        return redirect()->route('profile.edit');
    }

    /**
     * Update the developer profile.
     */
    public function update(DeveloperProfileUpdateRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validated();

        $profile = $user->developerProfile ?? new DeveloperProfile(['user_id' => $user->id]);

        // Handle CV upload
        if ($request->hasFile('cv')) {
            // Delete old CV if exists
            if ($profile->cv_path && Storage::disk('private')->exists($profile->cv_path)) {
                Storage::disk('private')->delete($profile->cv_path);
            }

            $cvPath = $request->file('cv')->store('cvs', 'private');
            $validated['cv_path'] = $cvPath;
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($profile->profile_photo_path && Storage::disk('public')->exists($profile->profile_photo_path)) {
                Storage::disk('public')->delete($profile->profile_photo_path);
            }

            $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $photoPath;
        }

        // Remove file inputs from validated data if not present
        unset($validated['cv'], $validated['profile_photo']);

        if ($profile->exists) {
            $profile->update($validated);
        } else {
            $user->developerProfile()->create($validated);
        }

        return back()->with('message', 'Profile updated successfully.');
    }

    /**
     * Download the developer's CV.
     */
    public function downloadCv(): RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $profile = $user->developerProfile;

        if (! $profile || ! $profile->cv_path) {
            return back()->with('error', 'CV not found.');
        }

        if (! Storage::disk('private')->exists($profile->cv_path)) {
            return back()->with('error', 'CV file not found.');
        }

        return Storage::disk('private')->download(
            $profile->cv_path,
            $user->name.'-CV.'.pathinfo($profile->cv_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Delete the developer's CV.
     */
    public function deleteCv(): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->developerProfile;

        if (! $profile || ! $profile->cv_path) {
            return back()->with('error', 'CV not found.');
        }

        if (Storage::disk('private')->exists($profile->cv_path)) {
            Storage::disk('private')->delete($profile->cv_path);
        }

        $profile->update(['cv_path' => null]);

        return back()->with('message', 'CV deleted successfully.');
    }

    /**
     * Delete the developer's profile photo.
     */
    public function deletePhoto(): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->developerProfile;

        if (! $profile || ! $profile->profile_photo_path) {
            return back()->with('error', 'Profile photo not found.');
        }

        if (Storage::disk('public')->exists($profile->profile_photo_path)) {
            Storage::disk('public')->delete($profile->profile_photo_path);
        }

        $profile->update(['profile_photo_path' => null]);

        return back()->with('message', 'Profile photo deleted successfully.');
    }
}
