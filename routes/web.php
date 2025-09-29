<?php

use Illuminate\Http\Request;
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
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PublicTicketController;

use App\Models\Client;
use App\Models\Coach;

// ---------------- Public Routes ----------------
Route::view('/', 'wc')->name('wc');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/Clientregistration', [ClientController::class, 'Clientregister'])->name('Clientregister');
Route::post('/ClientStore', [ClientController::class, 'ClientStore'])->name('ClientStore');

Route::get('/CoachRegistration', [CoachController::class, 'CoachRegister'])->name('CoachRegister');
Route::post('/CoachStore', [CoachController::class, 'CoachStore'])->name('CoachStore');

// Address picker
Route::get('/confirm-address', [AddressController::class, 'show'])->name('confirm.address');
Route::post('/confirm-address', [AddressController::class, 'store'])->name('confirm.address.store');

// Verification
Route::get('/client/verification-status', [ClientController::class, 'verificationStatus'])->name('client.verification.status');
Route::get('/verify-email/{code}', [ClientController::class, 'verifyEmail'])->name('verify-email');
Route::get('/coach/verify/{code}', [CoachController::class, 'verifyCoach'])->name('coach.verify-email');

// Public Tickets
Route::post('/tickets', [PublicTicketController::class, 'store'])->name('tickets.store');

// ---------------- Admin Routes ----------------
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/control', [AdminController::class, 'control'])->name('control');
    Route::post('/control/theme', [AdminController::class, 'updateTheme'])->name('control.updateTheme');

    Route::get('/transaction', [AdminController::class, 'transaction'])->name('transaction');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');

    // Notifications / Announcements
    Route::get('/notifications', [AdminController::class, 'fetchNotifications'])->name('notifications.fetch');
    Route::post('/send-notification', [AdminController::class, 'sendNotification'])->name('sendNotification');
    Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->name('announcement.store');

    // 2FA Passcode
    Route::post('/passcode/verify', [AdminController::class, 'verifyPasscode'])->name('passcode.verify');
    Route::post('/passcode/resend', [AdminController::class, 'resendPasscode'])->name('passcode.resend');

    // Verifications
    Route::post('/coach/verify/{coach}', [CoachController::class, 'verifyByAdmin'])->name('coach.verify');
    Route::get('/approve-client/{id}', [ClientController::class, 'approve'])->name('client.approve');
    Route::get('/approve-coach/{id}', [CoachController::class, 'approve'])->name('coach.approve');
    Route::post('/client/verify/{client}', [ClientController::class, 'verifyByAdmin'])->name('client.verify');
});


// ---------------- Client Auth Routes ----------------
Route::middleware(['auth:client'])->group(function () {
    Route::get('/client/home', [DashboardController::class, 'showDashboard'])->name('client.home');
    Route::get('/talent', [PageController::class, 'index'])->name('talent');

    // Profile posts
    Route::post('/client/profile-posts', [ClientProfilePostController::class, 'store'])->name('clientprofile_store');
    Route::get('/profile-posts/fetch', [ClientProfilePostController::class, 'fetchPosts'])->name('profile-posts.fetch');
    Route::delete('/profile-posts/{id}', [ClientProfilePostController::class, 'destroy'])->name('profile-posts.destroy');

    // Profile
    Route::get('/client/profile', [ClientController::class, 'profile'])->name('profile');
    Route::get('/client/profile/edit', [ClientController::class, 'profileedit'])->name('profile.edit');
    Route::put('/client/profile/update', [ClientController::class, 'updateProfile'])->name('client.profile.update');
    Route::put('/client/profile/password', [ClientController::class, 'updatePassword'])->name('client.profile.password.update');
    Route::post('/client/photo/upload', [ClientController::class, 'uploadPhoto'])->name('client.photo.upload');
    Route::get('/client/photo', [ClientController::class, 'fetchPhoto'])->name('profile.photo');

    // Status
    Route::post('/client/status/update', [ClientController::class, 'updateStatus'])->name('client.status.update');
});

// ---------------- Coach Auth Routes ----------------
Route::middleware(['auth:coach'])->group(function () {
    Route::get('/coach/home', [DashboardController::class, 'showDashboard'])->name('coach.home');
    Route::get('/talents', [CoachController::class, 'Talents'])->name('talents');

    // Profile Posts
    Route::post('/profile-posts', [CoachProfilePostController::class, 'store'])->name('coachprofile.store');
    Route::get('/coach-profile-posts/fetch', [CoachProfilePostController::class, 'fetchPosts'])->name('coachprofile.fetch');
    Route::delete('/profile-posts/{id}', [CoachProfilePostController::class, 'destroy'])->name('coachprofile.destroy');

    // Status
    Route::post('/coach/update-status', [CoachController::class, 'updateStatus'])->name('coach.updateStatus');

    // Profile Management
    Route::get('/coach/profile/edit', [CoachController::class, 'profileEdit'])->name('coach.profile.edit');
    Route::put('/coach/profile/update', [CoachController::class, 'updateProfile'])->name('coach.profile.update');

    // Photo
    Route::post('/coach/profile/photo', [CoachController::class, 'uploadPhoto'])->name('coach.photo.upload');
    Route::get('/coach/photo', [CoachController::class, 'fetchPhoto'])->name('coach.photo');
    Route::get('/coach/profile/photo', [CoachController::class, 'fetchPhoto'])->name('coach.profile.photo');
});

// ---------------- Shared Auth Routes ----------------
Route::middleware(['auth:client,coach'])->group(function () {
    // Appointments
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/appointmentdata', [AppointmentController::class, 'show'])->name('appointmentdata');

    Route::post('/appointments/{appointment:appointment_id}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{appointment:appointment_id}/decline', [AppointmentController::class, 'decline'])->name('appointments.decline');
    Route::post('/appointments/{appointment:appointment_id}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');

    // Optional GET fallbacks
    Route::get('/appointments/{appointment:appointment_id}/confirm', fn() => redirect()->route('appointmentdata')->with('error', 'Use the Confirm button.'));
    Route::get('/appointments/{appointment:appointment_id}/decline', fn() => redirect()->route('appointmentdata')->with('error', 'Use the Decline button.'));
    Route::get('/appointments/{appointment:appointment_id}/complete', fn() => redirect()->route('appointmentdata')->with('error', 'Use the Complete button.'));

    Route::get('/coach/{coachId}/appointment', [AppointmentController::class, 'showCoachAppointment'])->name('coach.appointment');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel')->middleware('auth:client');

    // Feedback
    Route::post('/appointments/{id}/feedback', [AppointmentController::class, 'submitFeedback'])->name('appointments.feedback');

    // Admin actions for clients
    Route::post('/admin/clients/{id}/approve', [ClientController::class, 'approveProfile'])->name('clients.approve');

    // Community posts comments
    Route::post('/community/posts/{postId}/comments', [CommunityPostController::class, 'addComment']);
});

// ---------------- Other Routes ----------------
Route::get('/coaches', [CoachController::class, 'index'])->name('coaches.index');
Route::get('/coach/profile', [CoachProfilePostController::class, 'profile'])->name('coach.profile');
Route::get('/coach/{coachId}/feedback', [FeedbackController::class, 'showFeedback'])->name('coach.feedback');
Route::get('/coach/{coach}/agreement', [ContractController::class, 'show'])->name('coach.agreement');

Route::get('/home', [DashboardController::class, 'home'])->name('home');

Route::get('/aboutsystem', [PageController::class, 'About'])->name('about');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');

Route::post('/ratings/store', [FeedbackController::class, 'store'])->name('ratings.store')->middleware('auth:client');

// Community
Route::get('/community/posts', [CommunityPostController::class, 'index'])->name('community.posts');
Route::post('/community/store', [CommunityPostController::class, 'store'])->name('community.store');
Route::delete('/community/posts/{id}', [CommunityPostController::class, 'destroy'])->name('community.delete');
Route::post('/community/posts/{id}/react', [CommunityPostController::class, 'react'])->name('community.react');
Route::get('/community/posts/{postId}/comments', [CommunityPostController::class, 'getComments']);

// Messages
Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
Route::post('/messages/send', [MessageController::class, 'store'])->name('messages.send');
Route::get('/messages/conversation/{receiverType}/{receiverId}', [MessageController::class, 'fetchMessages'])->name('messages.fetch');
Route::get('/messages/{id}/edit', [MessageController::class, 'edit'])->name('messages.edit');
Route::put('/messages/{id}', [MessageController::class, 'update'])->name('messages.update');
Route::delete('/messages/{id}', [MessageController::class, 'destroy'])->name('messages.destroy');

// Contracts
Route::post('/contracts/store-agreement/{coach_id}', [ContractController::class, 'storeAgreement'])->name('contracts.storeAgreement')->middleware('auth:client');
Route::get('/contracts/final', [ContractController::class, 'showFinalAgreement'])->name('contracts.final');

// Forget/Reset Password
Route::get('/forget-password', [LoginController::class, 'showForgetPasswordForm'])->name('forget.password');
Route::post('/forget-password', [LoginController::class, 'sendResetLinkEmail'])->name('forget.password.email');
Route::post('/forget-password/select', [LoginController::class, 'selectUser'])->name('forget.password.select');
Route::get('/reset-password/{user_type}/{token}', [LoginController::class, 'showResetForm'])->name('reset.password.form');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('reset.password.save');

// Announcements
Route::get('/announcements', [AnnouncementViewController::class, 'index'])->name('announcements.index');
Route::post('/clear-latest-announcement', fn() => session()->forget('latest_announcement'))->name('clear.latest.announcement');

// Welcome session clear
Route::post('/clear-welcome', function () {
    Session::forget('show_welcome');
    return response()->json(['success' => true]);
})->name('clear.welcome');

// Chatbot
Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
Route::post('/api/chat', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');

// Check email & username
Route::get('/check-email', function (Request $request) {
    $exists = Client::where('email', $request->email)->exists()
        || Coach::where('email', $request->email)->exists();
    return response()->json(['exists' => $exists]);
})->name('check.email');

Route::get('/check-username', [CoachController::class, 'checkUsername'])->name('check.username');

// User Profile
Route::get('/userprofile/{id}', [PageController::class, 'showProfile'])->name('user.profile');