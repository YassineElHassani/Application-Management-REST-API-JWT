<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return in_array($user->role, ['candidate', 'recruiter', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Application $application)
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'candidate') {
            return $application->user_id === $user->id;
        }
        
        if ($user->role === 'recruiter') {
            return $application->jobOffer->recruiter_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->role === 'candidate';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Application $application)
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'candidate') {
            return $application->user_id === $user->id && $application->status === Application::STATUS_PENDING;
        }
        
        if ($user->role === 'recruiter') {
            return $application->jobOffer->recruiter_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Application $application)
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'candidate') {
            return $application->user_id === $user->id && $application->status === Application::STATUS_PENDING;
        }
        
        return false;
    }
}