<?php

use App\Http\Controllers\CheckinController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Member\AuthController as MemberAuthController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\InvoiceController as MemberInvoiceController;
use App\Http\Controllers\Member\QrController as MemberQrController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberImportDownloadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Legacy Breeze login URL — staff use /staff/login (Filament).
Route::get('/login', fn () => abort(404));
Route::post('/login', fn () => abort(404));

Route::get('/', HomeController::class)->name('home');

// Public check-in routes (no auth — used by QR scanners and member phones)
Route::get('/checkin/{qrToken}', [CheckinController::class, 'scan'])
    ->name('checkin.scan')
    ->middleware('throttle:60,1');
Route::post('/checkin/{qrToken}/checkout', [CheckinController::class, 'checkout'])
    ->name('checkin.checkout')
    ->middleware('throttle:60,1');

Route::prefix('member')->group(function (): void {
    Route::middleware('guest:member')->group(function (): void {
        Route::get('login', [MemberAuthController::class, 'showLogin'])->name('member.login');
        Route::post('login', [MemberAuthController::class, 'login']);

        Route::get('set-password', [MemberAuthController::class, 'showSetPassword'])->name('member.set-password');
        Route::post('set-password', [MemberAuthController::class, 'setPassword'])->name('member.set-password.store');
    });

    Route::post('logout', [MemberAuthController::class, 'logout'])
        ->middleware('member.auth')
        ->name('member.logout');

    Route::middleware(['member.auth', 'member.verified'])->group(function (): void {
        Route::get('dashboard', [MemberDashboardController::class, 'index'])->name('member.dashboard');
        Route::get('qr/download', [MemberQrController::class, 'download'])->name('member.qr.download');
        Route::get('invoices/{invoice}/pdf', [MemberInvoiceController::class, 'pdf'])->name('member.invoices.pdf');
    });
});

Route::get('/dashboard', function () {
    return redirect(auth()->user()->defaultDashboardUrl());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('members/import/template', [MemberImportDownloadController::class, 'template'])
        ->name('members.import.template');
    Route::get('members/import/errors/{token}', [MemberImportDownloadController::class, 'errorReport'])
        ->name('members.import.errors');

    Route::get('members/{member}/qr', [MemberController::class, 'qr'])->name('web.members.qr');
    Route::get('members/{member}/qr/download', [MemberController::class, 'qrDownload'])->name('web.members.qr.download');
    Route::resource('members', MemberController::class)->names([
        'index' => 'web.members.index',
        'create' => 'web.members.create',
        'store' => 'web.members.store',
        'show' => 'web.members.show',
        'edit' => 'web.members.edit',
        'update' => 'web.members.update',
        'destroy' => 'web.members.destroy',
    ]);
    Route::resource('subscriptions', SubscriptionController::class)->names([
        'index' => 'web.subscriptions.index',
        'create' => 'web.subscriptions.create',
        'store' => 'web.subscriptions.store',
        'show' => 'web.subscriptions.show',
        'edit' => 'web.subscriptions.edit',
        'update' => 'web.subscriptions.update',
        'destroy' => 'web.subscriptions.destroy',
    ]);
});

require __DIR__.'/auth.php';
require __DIR__.'/member.php';
