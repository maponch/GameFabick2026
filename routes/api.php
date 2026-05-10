<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    });
Route::middleware('throttle:20,1')->post('/login', [AuthController::class, 'login']);
    
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });    
    Route::delete('/user', [UserController::class, 'destroy']);

    Route::post('/email/send-otp',  [EmailVerificationController::class, 'send']);
    Route::post('/email/verify',    [EmailVerificationController::class, 'verify']);
    

        // Edition du profil
    Route::patch('/user/username', [UserController::class, 'updateUsername']);
    Route::patch('/user/password', [UserController::class, 'updatePassword']);
    Route::post('/user/photo', [UserController::class, 'updatePhoto']);
});

// Mot de passe oublié (public)
Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp']);
Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

//restauration du comppte
Route::post('/user/restore', [UserController::class, 'restore']);


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::patch('/users/{user}/role', [AdminController::class, 'updateRole']);
    Route::post('/users/{user}/suspend', [AdminController::class, 'suspend']);
    Route::patch('/users/{user}/unsuspend', [AdminController::class, 'unsuspend']);
    Route::delete('/users/{user}', [AdminController::class, 'destroy']);
    Route::get('/users/{user}', [AdminController::class, 'show']);
});