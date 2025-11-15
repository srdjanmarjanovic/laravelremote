<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Developers can view their own applications
        // HR can view applications to their positions
        return $user->isDeveloper() || $user->isHR() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Application $application): bool
    {
        // Admins can view all applications
        if ($user->isAdmin()) {
            return true;
        }

        // Applicant can view their own application
        if ($application->user_id === $user->id) {
            return true;
        }

        // HR can view applications to positions they manage
        if ($user->isHR() && $user->canManagePosition($application->position)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isDeveloper() && $user->hasCompleteProfile();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Application $application): bool
    {
        // Only applicant can update their own application (e.g., withdraw)
        return $application->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Application $application): bool
    {
        // Admins can delete any application
        if ($user->isAdmin()) {
            return true;
        }

        // Applicant can delete their own application
        return $application->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the application status.
     */
    public function updateStatus(User $user, Application $application): bool
    {
        // Admins can update any application status
        if ($user->isAdmin()) {
            return true;
        }

        // HR can update status of applications to positions they manage
        return $user->isHR() && $user->canManagePosition($application->position);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Application $application): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Application $application): bool
    {
        return $user->isAdmin();
    }
}
