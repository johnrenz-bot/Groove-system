<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

use App\Models\Setting;
use App\Models\Admin;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // …
    }

    public function boot(): void
    {
        /**
         * ------------------------------------------------------------
         * Bootstrap a default Admin from .env (no tinker/seed needed)
         * ------------------------------------------------------------
         * Controlled by ADMIN_BOOTSTRAP (defaults to true in local).
         * Uses Admin::$casts['password' => 'hashed'] to auto-hash.
         */
        $shouldBootstrap = (bool) env('ADMIN_BOOTSTRAP', app()->environment('local'));

        if ($shouldBootstrap) {
            try {
                // Skip silently if migrations haven't run yet
                if (Schema::hasTable('admins')) {
                    $name     = env('ADMIN_NAME', 'Admin User');
                    $username = env('ADMIN_USERNAME', 'adminuser');
                    $email    = env('ADMIN_EMAIL', 'admin@example.com');
                    $password = env('ADMIN_PASSWORD', 'adminpassword'); // plain; auto-hash via cast

                    // Idempotent: update if exists, create if not
                    Admin::updateOrCreate(
                        ['email' => $email],
                        ['name' => $name, 'username' => $username, 'password' => $password]
                    );
                }
            } catch (\Throwable $e) {
                // Don’t crash app if DB not ready; just log
                Log::warning('Admin bootstrap skipped: '.$e->getMessage());
            }
        }

        /**
         * ------------------------------------------------------------
         * Your existing shared view data
         * ------------------------------------------------------------
         */
        $this->app->booted(function () {
            View::composer('*', function ($view) {

                static $theme = null;
                if ($theme === null) {
                    try {
                        $theme = Setting::get('theme', 'light');
                    } catch (\Throwable $e) {
                        $theme = 'light';
                    }
                }
                $view->with('appTheme', $theme);

                // ---- Session/Auth shares (as before) ----
                if (Session::has('client')) {
                    $view->with('client', Session::get('client'));
                }

                if (Session::has('coach')) {
                    $view->with('coach', Session::get('coach'));
                }

                if (Auth::guard('admin')->check()) {
                    $view->with('admin', Auth::guard('admin')->user());
                }
            });
        });
    }
}
