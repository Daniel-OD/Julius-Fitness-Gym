<?php

use App\Http\Controllers\Member\Auth\RegisterController;
use App\Http\Controllers\Member\Auth\VerifyEmailController;
use App\Http\Controllers\Member\PlansController;
use Illuminate\Support\Facades\Route;

Route::prefix('member')->name('member.')->group(function (): void {

    // Registration (guest-only)
    Route::middleware('guest:member')->group(function (): void {
        Route::get('/register', [RegisterController::class, 'show'])->name('register');
        Route::post('/register', [RegisterController::class, 'register']);
    });

    // Authenticated routes
    Route::middleware('member.auth')->group(function (): void {
        Route::get('/verify-email', [VerifyEmailController::class, 'show'])->name('verify-email');
        Route::post('/email/verification-notification', [VerifyEmailController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        // Requires verified email
        Route::middleware('member.verified')->group(function (): void {
            Route::get('/plans', [PlansController::class, 'index'])->name('plans');
            Route::post('/plans', [PlansController::class, 'store'])->name('plans.store');
        });
    });

    // Signed email verification link
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['member.auth', 'signed', 'throttle:6,1'])
        ->name('verification.verify');
});
