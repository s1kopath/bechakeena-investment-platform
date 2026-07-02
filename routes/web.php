<?php

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Public\Home;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (Blade + Livewire, server-rendered for SEO) — Phase 3
|--------------------------------------------------------------------------
*/
Route::get('/', Home::class)->name('home');

/*
|--------------------------------------------------------------------------
| Investor dashboard (React + Inertia) — Phase 5
|--------------------------------------------------------------------------
| Behind 'auth' + 'verified': unverified users are bounced to the
| email-verification notice (routes/auth.php).
*/
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', DashboardIndex::class)->name('index');
});
