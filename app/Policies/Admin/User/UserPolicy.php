<?php

namespace App\Policies\Admin\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user = null)
    {
        $authUser = $user ?? Auth::user();
        return $authUser && $authUser->type === 'Admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user = null, ?User $model = null)
    {
        $authUser = $user ?? Auth::user();
        return $authUser && $authUser->type === 'Admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user = null)
    {
        $authUser = $user ?? Auth::user();
        return $authUser && $authUser->type === 'Admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user = null, ?User $model = null)
    {
        $authUser = $user ?? Auth::user();
        return $authUser && $authUser->type === 'Admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user = null, ?User $model = null)
    {
        $authUser = $user ?? Auth::user();
        return $authUser && $authUser->type === 'Admin';
    }
}
