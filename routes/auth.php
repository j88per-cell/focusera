<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Support\RegistrationAvailability;

Route::middleware('guest')->group(function () {
    if (app(RegistrationAvailability::class)->shouldExposeRegistrationRoute()) {
        Route::get('register', [RegisteredUserController::class, 'create'])
            ->name('register');

        Route::post('register', [RegisteredUserController::class, 'store']);
    }

    // This app uses a login modal; redirect and signal UI to open it
    Route::get('login', function () { return redirect('/')->with('open_login', true); })->name('login');

    //Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::get('verify-email', EmailVerificationPromptController::class)
    ->name('verification.notice');
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// DO NOT TOUCH - OTP requests cannot be restricted by user 
Route::post('/login/request-otp', [AuthenticatedSessionController::class, 'store']);
Route::post('/login/verify-otp', [AuthenticatedSessionController::class, 'verify']);
