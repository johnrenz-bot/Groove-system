<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Client;
use App\Models\Coach;
use App\Models\Admin;
use App\Notifications\ResetPassword;

class LoginController extends Controller
{
    // ---------------------------
    // Login (unchanged examples)
    // ---------------------------
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check())  return redirect()->route('admin.AdminDashboard');
        if (Auth::guard('client')->check()) return redirect()->route('client.home');
        if (Auth::guard('coach')->check())  return redirect()->route('coach.home');
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Admin login
        $admin = Admin::where('username', $request->username)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            Auth::guard('admin')->login($admin);
            Session::put('admin', $admin);

            // optional passcode logic
            session(['require_admin_passcode' => true]);
            app(\App\Http\Controllers\AdminController::class)->issuePasscode($admin);

            return redirect()->route('admin.AdminDashboard');
        }

        // Client login
        $client = Client::where('username', $request->username)->first();
        if ($client && Hash::check($request->password, $client->password)) {
            if (!$client->email_verified) {
                return back()->withErrors(['login_error' => 'Please verify your email before logging in.'])->withInput();
            }
            Auth::guard('client')->login($client);
            Session::put('client', $client);
            Session::put('show_welcome', true);
            return redirect()->route('client.home');
        }

        // Coach login
        $coach = Coach::where('username', $request->username)->first();
        if ($coach && Hash::check($request->password, $coach->password)) {
            if (!$coach->email_verified) {
                return back()->withErrors(['login_error' => 'Please verify your email before logging in.'])->withInput();
            }
            Auth::guard('coach')->login($coach);
            Session::put('coach', $coach);
            Session::put('show_welcome', true);
            return redirect()->route('coach.home');
        }

        // Invalid
        return back()->withErrors(['login_error' => 'Invalid username or password.'])->withInput();
    }

    // --------------------------------
    // Forgot / Reset Password (ALL)
    // --------------------------------

    // Show forget password form
    public function showForgetPasswordForm()
    {
        return view('ForgetPassword');
    }

    // Handle email input and send reset link
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $clients = Client::where('email', $request->email)->get();
        $coaches = Coach::where('email', $request->email)->get();
        $admins  = Admin::where('email', $request->email)->get();

        $totalUsers = $clients->count() + $coaches->count() + $admins->count();

        if ($totalUsers === 0) {
            return back()->withErrors(['email' => 'No account found with this email.']);
        }

        // Multi-user email → show selector
        if ($totalUsers > 1) {
            $users = $clients->concat($coaches)->concat($admins);
            return view('ForgetPassword', [
                'email' => $request->email,
                'users' => $users,
            ]);
        }

        // Single user → generate token + notify
        $user = $clients->first() ?? $coaches->first() ?? $admins->first();

        $userType = $user instanceof Coach
            ? 'coach'
            : ($user instanceof Client ? 'client' : 'admin');

        $userId = $user->getKey(); // generic for all models

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email, 'user_type' => $userType, 'user_id' => $userId],
            ['token' => $token, 'created_at' => now()]
        );

        $user->notify(new ResetPassword($token, $userType));

        return back()->with([
            'status'     => 'Password reset link sent to your email.',
            'emailSent'  => true,
        ]);
    }

    // Select one account when email belongs to multiple users
    public function selectUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'user_type' => 'required|in:client,coach,admin',
        ]);

        $model = $request->user_type === 'coach'
            ? Coach::class
            : ($request->user_type === 'client' ? Client::class : Admin::class);

        $user = $model::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false], 404);
        }

        $token  = Str::random(64);
        $userId = $user->getKey();

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email, 'user_type' => $request->user_type, 'user_id' => $userId],
            ['token' => $token, 'created_at' => now()]
        );

        $user->notify(new ResetPassword($token, $request->user_type));

        return response()->json(['success' => true]);
    }

    // Handle password reset submission
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'user_type' => 'required|in:client,coach,admin',
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $model = $request->user_type === 'coach'
            ? Coach::class
            : ($request->user_type === 'client' ? Client::class : Admin::class);

        // verify token + user_type + expiry (60 min)
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('user_type', $request->user_type)
            ->where('token', $request->token)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        $user = $model::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')
            ->where('email', $request->email)
            ->where('user_type', $request->user_type)
            ->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset successfully!');
    }

    // Show reset form from email link
    public function showResetForm($user_type, $token, Request $request)
    {
        $email = $request->query('email');

        return view('ForgetPassword', [
            'token'     => $token,
            'email'     => $email,
            'user_type' => $user_type,
        ]);
    }

    // Logout (example)
    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            Session::forget('admin');
            $redirectRoute = 'login';
        } elseif (Auth::guard('client')->check()) {
            Auth::guard('client')->logout();
            Session::forget('client');
            $redirectRoute = 'login';
        } elseif (Auth::guard('coach')->check()) {
            Auth::guard('coach')->logout();
            Session::forget('coach');
            $redirectRoute = 'login';
        } else {
            $redirectRoute = 'login';
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($redirectRoute);
    }
}
