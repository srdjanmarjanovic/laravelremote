<?php

namespace App\Policies;

use App\Models\Position;
use App\Models\User;

class PositionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isHR() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Position $position): bool
    {
        // Admins can view all positions
        if ($user->isAdmin()) {
            return true;
        }

        // HR users can view positions from their companies
        if ($user->isHR()) {
            return $user->canManagePosition($position);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only HR users with at least one company can create positions
        return $user->isHR() && $user->companies()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Position $position): bool
    {
        // Admins can update all positions
        if ($user->isAdmin()) {
            return true;
        }

        // HR users can update positions from their companies
        return $user->isHR() && $user->canManagePosition($position);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Position $position): bool
    {
        // Admins can delete all positions
        if ($user->isAdmin()) {
            return true;
        }

        // HR users can delete positions from their companies
        return $user->isHR() && $user->canManagePosition($position);
    }

    /**
     * Determine whether the user can feature the position.
     */
    public function feature(User $user, Position $position): bool
    {
        // Only admins can feature positions
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can archive the position.
     */
    public function archive(User $user, Position $position): bool
    {
        // Admins can archive any position
        if ($user->isAdmin()) {
            return true;
        }

        // HR users can archive their own positions
        return $user->isHR() && $user->canManagePosition($position);
    }

    /**
     * Determine whether the user can apply to the position.
     */
    public function apply(User $user, Position $position): bool
    {
        // Only developers can apply
        if (! $user->isDeveloper()) {
            return false;
        }

        // Position must accept applications
        if (! $position->canReceiveApplications()) {
            return false;
        }

        // Developer must have complete profile
        if (! $user->hasCompleteProfile()) {
            return false;
        }

        // Check if user already applied
        return ! $position->applications()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Position $position): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Position $position): bool
    {
        return $user->isAdmin();
    }
}
