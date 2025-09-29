<?php

namespace App\Providers;

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
        // Model::class => Policy::class,
        // Halimbawa:
        // 'App\Models\Post' => 'App\Policies\PostPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * ✅ Gate para lang sa mga admin.
         *
         * Gumagamit ng `is_admin` boolean column sa users table.
         * Baguhin ang logic kung role/permission package ang gamit mo.
         */
        Gate::define('access-admin', function ($user) {
            return (bool) ($user->is_admin ?? false);
        });
    }
}
