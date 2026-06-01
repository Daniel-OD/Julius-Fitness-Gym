<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// NOTE: auth middleware to be added by the backend agent once authentication is in place.
Route::view('/dashboard', 'dashboard')->name('dashboard');
