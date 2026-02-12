<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentSendController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserPlanController;
use App\Http\Controllers\SmtpSettingsController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckQuota;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DigiSign Web Routes
|--------------------------------------------------------------------------
*/

// ─── Public Routes ──────────────────────────────────────────────────────────

// Home — redirect to login or dashboard
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('documents.index')
        : redirect()->route('login');
})->name('home');

// Dashboard — redirect to documents (for SSO compatibility)
Route::get('/dashboard', function () {
    return redirect()->route('documents.index');
})->name('dashboard')->middleware('auth');

// Public Verification
Route::get('/verify/{hash}', [VerificationController::class, 'show'])
    ->name('verify.show');

// SSO Auto-Login (signed URL from Web-Tools)
Route::get('/sso/autologin', [ApiController::class, 'ssoAutoLogin'])
    ->middleware('signed')
    ->name('sso.autologin');

// Admin API SSO endpoint for Web-Tools integration
// This route is outside CSRF protection
Route::post('/admin-api/sso/login', [ApiController::class, 'ssoLogin'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ─── Guest Routes ───────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::post('/sso/login', [AuthController::class, 'ssoLogin'])->name('sso.login');
});

// ─── Authenticated Routes ───────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create')->middleware(CheckQuota::class);
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store')->middleware(CheckQuota::class);
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/{document}/pdf', [DocumentController::class, 'servePdf'])->name('documents.pdf');
    
    // Document Sending with Recipients
    Route::get('/documents/send/new', [DocumentSendController::class, 'showForm'])->name('documents.send.form')->middleware(CheckQuota::class);
    Route::post('/documents/send', [DocumentSendController::class, 'sendDocument'])->name('documents.send')->middleware(CheckQuota::class);
    
    // Self-Signing
    Route::get('/documents/{document}/sign', [DocumentController::class, 'sign'])->name('documents.sign');
    Route::post('/documents/{document}/sign', [DocumentController::class, 'processSign'])->name('documents.processSign');

    // User Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create')->middleware(CheckQuota::class);
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware(CheckQuota::class);
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');

    // User Plan & Usage
    Route::get('/my-plan', [PlanController::class, 'index'])->name('plan.index');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // ─── Admin Routes ───────────────────────────────────────────────────────
    Route::middleware(AdminMiddleware::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        // Documents management
        Route::get('/documents', [AdminController::class, 'documents'])->name('documents');

        // Category Management (admin)
        Route::get('/categories', [CategoryController::class, 'adminIndex'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'adminCreate'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'adminStore'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'adminEdit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'adminUpdate'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'adminDestroy'])->name('categories.destroy');

        // Subscription Plans Management
        Route::resource('plans', SubscriptionPlanController::class);

        // User Plans Management
        Route::get('/users/plans', [UserPlanController::class, 'index'])->name('users.plans');
        Route::post('/users/{user}/plan', [UserPlanController::class, 'updatePlan'])->name('users.plan.update');

        // SMTP Settings
        Route::get('/smtp', [SmtpSettingsController::class, 'index'])->name('smtp.index');
        Route::post('/smtp', [SmtpSettingsController::class, 'update'])->name('smtp.update');
        Route::post('/smtp/test', [SmtpSettingsController::class, 'test'])->name('smtp.test');
    });
});

// ─── Public Document Signing (Token-based) ──────────────────────────────────
Route::get('/sign/{token}', [DocumentSendController::class, 'signByToken'])->name('documents.sign.token');
Route::post('/sign/{token}/verify-otp', [DocumentSendController::class, 'verifyOtp'])->name('documents.sign.token.verify-otp');
Route::post('/sign/{token}/resend-otp', [DocumentSendController::class, 'resendOtp'])->name('documents.sign.token.resend-otp');
Route::post('/sign/{token}', [DocumentSendController::class, 'processSignByToken'])->name('documents.sign.token.process');
Route::get('/sign/{token}/pdf', [DocumentSendController::class, 'servePdfByToken'])->name('documents.sign.token.pdf');
