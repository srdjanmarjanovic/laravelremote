<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
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
    public function view(User $user, Company $company): bool
    {
        return $user->isAdmin() || $user->canAccessCompany($company);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isHR();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        return $user->isAdmin() || $user->canAccessCompany($company);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {
        // Only admins or company owners can delete
        if ($user->isAdmin()) {
            return true;
        }

        $membership = $company->users()->where('user_id', $user->id)->first();

        return $membership && $membership->pivot->role === 'owner';
    }

    /**
     * Determine whether the user can manage team members.
     */
    public function manageTeam(User $user, Company $company): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $membership = $company->users()->where('user_id', $user->id)->first();

        return $membership && in_array($membership->pivot->role, ['owner', 'admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }
}
