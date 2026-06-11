<?php

use App\Http\Controllers\Member\Auth\RegisterController;
use App\Http\Controllers\Member\Auth\VerifyEmailController;
use App\Http\Controllers\Member\MemberPlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('member')->name('member.')->group(function (): void {

    Route::middleware('guest:member')->group(function (): void {
        Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
        Route::post('/register', [RegisterController::class, 'register']);
    });

    Route::middleware('member.auth')->group(function (): void {
        Route::get('/verify-email', [VerifyEmailController::class, 'showVerifyEmail'])->name('verify-email');
        Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('verification.resend');

        Route::middleware('member.verified')->group(function (): void {
            Route::get('/plans', [MemberPlanController::class, 'index'])->name('plans');
            Route::post('/plans/{plan}', [MemberPlanController::class, 'select'])->name('plans.select');
        });
    });

    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['member.auth', 'signed', 'throttle:6,1'])
        ->name('verification.verify');
});
