<?php

use App\Http\Controllers\Dashboard\DashboardController;
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
| TODO(Phase 1): wrap this group in ['auth', 'verified'] middleware once
| authentication exists. Left open in Phase 0 to smoke-test Inertia.
*/
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
});
