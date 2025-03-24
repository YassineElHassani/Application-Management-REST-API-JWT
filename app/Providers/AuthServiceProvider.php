<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\JobOffer;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\JobOfferPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        JobOffer::class => JobOfferPolicy::class,
        Application::class => ApplicationPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('is-recruiter', function (User $user) {
            return $user->role === 'recruiter' || $user->role === 'admin';
        });

        Gate::define('is-candidate', function (User $user) {
            return $user->role === 'candidate';
        });
    }
}