<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementViewController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientProfilePostController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\CoachProfilePostController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PublicTicketController;


use App\Models\Appointment;
use App\Models\Client;
use App\Models\Coach;

Route::view('/', 'wc')->name('wc');

 Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    
Route::get('/Clientregistration', [ClientController::class, 'Clientregister'])->name('Clientregister');

    Route::post('/ClientStore', [ClientController::class, 'ClientStore'])->name('ClientStore');


    Route::get('/CoachRegistration', [CoachController::class, 'CoachRegister'])->name('CoachRegister');
    Route::post('/CoachStore', [CoachController::class, 'CoachStore'])->name('CoachStore');

    
// Address picker
Route::get('/confirm-address',  [AddressController::class, 'show'])->name('confirm.address');
Route::post('/confirm-address', [AddressController::class, 'store'])->name('confirm.address.store');

Route::get('/verify-email/{code}', [ClientController::class, 'verifyEmail'])->name('verify-email');

        Route::get('/coach/verify/{code}', [CoachController::class, 'verifyCoach'])->name('coach.verify-email');


Route::post('/tickets', [PublicTicketController::class, 'store'])->name('tickets.store');

    // ---- Admin area
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('AdminDashboard');
    Route::get('/users',     [AdminController::class, 'users'])->name('Adminusers');
    Route::get('/control',   [AdminController::class, 'control'])->name('Admincontrol');
    Route::post('/control/theme', [AdminController::class, 'updateTheme'])->name('control.updateTheme');

    Route::get('/transaction', [AdminController::class, 'transaction'])->name('Admintransaction');
    Route::get('/reports',     [AdminController::class, 'reports'])->name('Adminreports');

    Route::get('/tickets',      [AdminController::class, 'tickets'])->name('Admintickets');


    // Notifications / Announcements
    Route::get('/notifications/fetch', [AdminController::class, 'fetchNotifications'])->name('notifications.fetch');
    Route::post('/announcement/store', [AdminController::class, 'storeAnnouncement'])->name('announcement.store');

    // 2FA Passcode
    Route::post('/passcode/verify',  [AdminController::class, 'verifyPasscode'])->name('passcode.verify');
    Route::post('/passcode/resend',  [AdminController::class, 'resendPasscode'])->name('passcode.resend');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



Route::middleware(['auth:client'])->group(function () {
    Route::get('/client/home', [DashboardController::class, 'showDashboard'])->name('client.home');

    Route::get('/talents', [PageController::class, 'index'])->name('talent');

    Route::post('/client/profile-posts', [ClientProfilePostController::class, 'store'])->name('clientprofile_store');
    Route::get('/profile-posts/fetch', [ClientProfilePostController::class, 'fetchPosts'])->name('profile-posts.fetch');
    Route::delete('/profile-posts/{id}', [ClientProfilePostController::class, 'destroy'])->name('profile-posts.destroy');

    Route::post('/client/status/update', [ClientController::class, 'updateStatus'])->name('client.status.update');

    Route::get('/client/profile', [ClientController::class, 'profile'])->name('profile');
    Route::get('/client/profile/EDIT', [ClientController::class, 'profileedit'])->name('profile.edit');
    Route::put('/client/profile/update', [ClientController::class, 'updateProfile'])->name('client.profile.update');
    Route::post('/client/photo/upload', [ClientController::class, 'uploadPhoto'])->name('client.photo.upload');
    Route::get('/client/photo', [ClientController::class, 'fetchPhoto'])->name('profile.photo');
});




Route::get('/coaches', [CoachController::class, 'index'])->name('coaches.index');


// routes/web.php
Route::get('/coach/profile', [CoachProfilePostController::class, 'profile'])->name('Profile');

Route::get('/home', [DashboardController::class, 'home'])->name('home');


Route::get('/Aboutsystem', [PageController::class, 'About'])->name('About');
Route::get('/Aboutsystem', [PageController::class, 'About'])->name('about');

Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
Route::post('/messages/send', [MessageController::class, 'store'])->name('messages.send');
Route::get('/messages/conversation/{receiverType}/{receiverId}', [MessageController::class, 'fetchMessages'])->name('messages.fetch');
Route::get('/messages/{id}/edit', [MessageController::class, 'edit'])->name('messages.edit');
Route::put('/messages/{id}', [MessageController::class, 'update'])->name('messages.update');
Route::delete('/messages/{id}', [MessageController::class, 'destroy'])->name('messages.destroy');

// Community Posts
Route::post('/community/posts/{id}/react', [CommunityPostController::class, 'react']);
Route::get('/community/posts/{postId}/comments', [CommunityPostController::class, 'getComments']);
Route::get('/community/posts', [CommunityPostController::class, 'index'])->name('community.posts');

Route::post('/community/store', [CommunityPostController::class, 'store'])->name('community.store');



Route::delete('/community/posts/{id}', [CommunityPostController::class, 'destroy'])->name('community.delete');

Route::middleware(['web'])->group(function () {

    Route::middleware(['auth:client,coach'])->group(function () {
        // Create & list
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/appointmentdata', [AppointmentController::class, 'show'])->name('appointmentdata');

        // Feedback
        Route::post('/appointments/{id}/feedback', [AppointmentController::class, 'submitFeedback'])
            ->name('appointments.feedback');

        // ✅ Action routes as POST (no spoofing headaches)
        Route::post('/appointments/{appointment:appointment_id}/confirm', [AppointmentController::class, 'confirm'])
             ->name('appointments.confirm');

          // ✅ Decline (coach-only)
        Route::post('/appointments/{appointment:appointment_id}/decline', [AppointmentController::class, 'decline'])
            ->name('appointments.decline');

            

     

        Route::post('/appointments/{appointment:appointment_id}/complete', [AppointmentController::class, 'complete'])
            ->name('appointments.complete');

        // ✅ Optional GET fallbacks (prevent MethodNotAllowed if someone visits the URL)
        Route::get('/appointments/{appointment:appointment_id}/confirm', function () {
            return redirect()->route('appointmentdata')->with('error', 'Use the Confirm button to perform this action.');
        });

       // ✅ Optional GET fallback
        Route::get('/appointments/{appointment:appointment_id}/decline', function () {
            return redirect()->route('appointmentdata')->with('error', 'Use the Decline button to perform this action.');
        });


        Route::get('/appointments/{appointment:appointment_id}/complete', function () {
            return redirect()->route('appointmentdata')->with('error', 'Use the Complete button to perform this action.');
        });

        // Coach page
        Route::get('/coach/{coachId}/appointment', [AppointmentController::class, 'showCoachAppointment'])
            ->name('coach.appointment');

        // Admin
        Route::post('/admin/clients/{id}/approve', [ClientController::class, 'approveProfile'])
            ->name('clients.approve');

        // Client settings
        Route::put('/client/profile/password', [ClientController::class, 'updatePassword'])
            ->name('client.profile.password.update')
            ->middleware('auth:client');
    });

});

Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');

Route::post('/ratings/store', [FeedbackController::class, 'store'])->name('ratings.store')->middleware('auth:client');
Route::get('/coach/{coachId}/feedback', [FeedbackController::class, 'showFeedback'])->name('coach.feedback');

Route::middleware('auth:coach')->group(function () {
    // ---------------- Dashboard & Talents ----------------
    Route::get('/coach/home', [DashboardController::class, 'showDashboard'])->name('coach.home');
    Route::get('/Talents', [CoachController::class, 'Talents'])->name('Talent');

    // ---------------- Profile Posts ----------------
    Route::post('/profile-posts', [CoachProfilePostController::class, 'store'])->name('Coachprofile.Store');
    Route::get('/coach-profile-posts/fetch', [CoachProfilePostController::class, 'fetchPosts'])->name('coach-profile-posts.fetch');
    Route::delete('/profile-posts/{id}', [CoachProfilePostController::class, 'destroy'])->name('coach-profile-posts.destroy');

    // ---------------- Coach Status ----------------
    Route::post('/coach/update-status', [CoachController::class, 'updateStatus'])->name('coach.updateStatus');

    // ---------------- Profile Management ----------------
Route::get('/coach/profile/edit', [CoachController::class, 'profileEdit'])->name('PROFILE.EDIT');
    Route::put('/coach/profile/update', [CoachController::class, 'updateProfile'])->name('COACH.UPDATE');

    // ---------------- Photo Upload & Fetch ----------------
    Route::post('/coach/profile/photo', [CoachController::class, 'uploadPhoto'])->name('coach.profile.uploadPhoto');
    Route::get('/coach/photo', [CoachController::class, 'fetchPhoto'])->name('PROFILE.PHOTO');
    Route::get('/coach/profile/photo', [CoachController::class, 'fetchPhoto'])->name('coach.profile.photo');
});

Route::middleware(['auth:client,coach'])->group(function () {
    Route::post('/community/posts/{postId}/comments', [CommunityPostController::class, 'addComment']);
});

Route::post('/community/posts/{id}/react', [CommunityPostController::class, 'react'])->name('community.posts.react');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.AdminDashboard');

    
    Route::get('/admin/users', [AdminController::class, 'users'])->name('Adminusers');
    

Route::get('/admin/control', [AdminController::class, 'control'])->name('admin.control');
Route::post('/admin/theme', [AdminController::class, 'updateTheme'])->name('admin.updateTheme');



Route::post('/admin/coach/verify/{coach}', [CoachController::class, 'verifyByAdmin'])
     ->name('admin.coach.verify')
     ->middleware('auth:admin');

    Route::get('/admin/approve-client/{id}', [ClientController::class, 'approve'])->name('admin.client.approve');
    Route::get('/admin/approve-coach/{id}', [CoachController::class, 'approve'])->name('admin.coach.approve');
});

// Terms and Conditions page
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::middleware(['auth:admin'])->group(function () {
// routes/web.php
Route::get('/admin/notifications', [\App\Http\Controllers\AdminController::class, 'fetchNotifications'])
    ->name('admin.notifications.fetch')->middleware('auth:admin');

    Route::post('/admin/send-notification', [App\Http\Controllers\AdminController::class, 'sendNotification'])->name('admin.sendNotification');
});


Route::post('/admin/client/verify/{client}', [ClientController::class, 'verifyByAdmin'])
    ->name('admin.client.verify')
    ->middleware('auth:admin');


Route::get('/userprofile/{id}', [PageController::class, 'showProfile'])->name('user.profile');

// Check username
Route::get('/check-username', function (\Illuminate\Http\Request $request) {
    $exists = \App\Models\Coach::where('username', $request->username)->exists();
    return response()->json(['exists' => $exists]);
});

Route::get('/check-email', function (Request $request) {
    $exists = Client::where('email', $request->email)->exists()
        || Coach::where('email', $request->email)->exists();

    return response()->json(['exists' => $exists]);
});

// Check username
Route::get('/check-username', function (Request $request) {
    $exists = Client::where('username', $request->username)->exists();
    return response()->json(['exists' => $exists]);
});



// Store agreement form (POST)
Route::post('/contracts/store-agreement/{coach_id}', [ContractController::class, 'storeAgreement'])
    ->name('contracts.storeAgreement')
    ->middleware('auth:client');

Route::get('/contracts/final', [ContractController::class, 'showFinalAgreement'])->name('contracts.final');

Route::get('/check-username', [CoachController::class, 'checkUsername']);

// Cancel appointment (POST)
Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
    ->name('appointments.cancel')
    ->middleware('auth:client'); // ensure only clients can cancel their appointments

Route::get('/admin/transaction', [AdminController::class, 'transaction'])->name('Admintransaction');

Route::post('/clear-welcome', function () {
    Session::forget('show_welcome');
    return response()->json(['success' => true]);
})->name('clear.welcome');





Route::get('/forget-password', [LoginController::class, 'showForgetPasswordForm'])->name('ForgetPassword');
Route::post('/forget-password', [LoginController::class, 'sendResetLinkEmail'])->name('ForgetPassword.email');
Route::post('/forget-password/select', [LoginController::class, 'selectUser'])->name('ForgetPassword.select');

Route::get('/reset-password/{user_type}/{token}', [LoginController::class, 'showResetForm'])->name('reset-password.form');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('reset-password.save');





Route::get('/coach/{coach}/agreement', [ContractController::class, 'show'])->name('coach.agreement');

Route::post('/admin/announcements', [\App\Http\Controllers\AdminController::class, 'storeAnnouncement'])
    ->name('admin.announcement.store')->middleware('auth:admin');
    
    Route::get('/announcements', [AnnouncementViewController::class, 'index'])->name('announcements.index');

Route::post('/clear-latest-announcement', function () {
    session()->forget('latest_announcement');
})->name('clear.latest_announcement');


Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
Route::post('/api/chat', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');

