<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyEmail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication routes (investor / web guard) — Phase 1
|--------------------------------------------------------------------------
| Investor-facing auth as Blade + Livewire full-page components. Admin auth
| is a SEPARATE guard/login under /admin (routes/admin.php, React + Inertia)
| — built in Phase 2, kept fully separate.
|
| Still to come this phase: password reset, Google OAuth (Socialite),
| profile, KYC upload.
|
| Spec: docs/04-features/02-auth-accounts-kyc.md
*/

Route::middleware('guest')->group(function () {
    Route::get('register', Register::class)->name('register');
    Route::get('login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    // Email verification
    Route::get('verify-email', VerifyEmail::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('logout', LogoutController::class)->name('logout');
});
