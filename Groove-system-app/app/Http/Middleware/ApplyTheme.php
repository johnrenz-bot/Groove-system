<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ApplyTheme
{
    public function handle(Request $request, Closure $next)
    {
        $theme = Setting::get('theme', 'light');
        View::share('appTheme', $theme);

        // Optional cookie (not used by blades; just for client scripts if needed)
        cookie()->queue(cookie('app_theme', $theme, 60 * 24 * 365));

        return $next($request);
    }
}
