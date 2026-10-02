<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login/email', [AuthController::class, 'emailLogin'])
    ->name('login.email');

Route::post('/login/send-otp', [AuthController::class, 'sendOtp'])
    ->name('login.sendOtp');

Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp'])
    ->name('login.verifyOtp');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


Route::get('/forgot-password', function () {
    return view('auth.forgot_password');
})->name('password.request');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset_password', [
        'token' => $token,
        'email' => request('email'),
    ]);
})->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.update');