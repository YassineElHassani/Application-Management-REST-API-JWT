<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model)
    {
        if ($user->id === $model->id) {
            return true;
        }
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'recruiter' && $model->role === 'candidate') {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model)
    {
        if ($user->id === $model->id) {
            return true;
        }
        
        if ($user->role === 'admin') {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model)
    {
        if ($user->id === $model->id) {
            return true;
        }
        
        if ($user->role === 'admin') {
            return true;
        }
        
        return false;
    }
}