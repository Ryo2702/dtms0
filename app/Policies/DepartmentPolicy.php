<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Department $department): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Department $department): bool
    {
        return $user->hasRole('Admin');
    }


    public function create(User $user): bool
    {
        // only admins can create
        return $user->hasRole('Admin');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasRole('Admin');
    }

    public function edit(User $user): bool
    {
        return $user->hasRole('Admin');
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Department $department): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Department $department): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Department $department): bool
    {
        return false;
    }
}
