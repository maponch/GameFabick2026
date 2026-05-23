<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\GameTemplateController as AdminGameTemplateController;
use App\Http\Controllers\Api\Admin\ObjectController as AdminObjectController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\Game\GameTemplateController;
use App\Http\Controllers\Api\Game\ProjectController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\TwoFactorController;
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

    Route::post('/2fa/generate-totp', [TwoFactorController::class, 'generateTotp']);
    Route::post('/2fa/send-email',    [TwoFactorController::class, 'sendEmailOtp']);
    Route::post('/2fa/enable',        [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable',       [TwoFactorController::class, 'disable']);
    
    Route::get('/templates', [GameTemplateController::class, 'index']);
    Route::get('/templates/{slug}', [GameTemplateController::class, 'show']);

    Route::get('/projects',                [ProjectController::class, 'index']);
    Route::post('/projects',               [ProjectController::class, 'store']);
    Route::get('/projects/{project}',      [ProjectController::class, 'show']);
    Route::delete('/projects/{project}',   [ProjectController::class, 'destroy']);
    Route::post('/projects/find-similar', [ProjectController::class, 'findSimilar']);
});

Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);

// Mot de passe oublié (public)
Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp']);
Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

//restauration du comppte
Route::post('/user/restore', [UserController::class, 'restore']);


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminUserController::class, 'stats']);
    Route::get('/users', [AdminUserController::class, 'users']);
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole']);
    Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend']);
    Route::patch('/users/{user}/unsuspend', [AdminUserController::class, 'unsuspend']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::post('/users/{user}/regenerate-2fa-codes', [AdminUserController::class, 'regenerateRecoveryCodes']);
    Route::post('/users/{user}/disable-2fa',          [AdminUserController::class, 'disableTwoFactor']);
    Route::post('/users/{user}/cancel-deletion', [AdminUserController::class, 'cancelDeletion'])
    ->withTrashed();

    Route::get('/templates', [AdminGameTemplateController::class, 'index']);
    Route::post('/templates', [AdminGameTemplateController::class, 'store']);
    Route::get('/templates/{template}', [AdminGameTemplateController::class, 'show']);
    Route::match(['put', 'patch'], '/templates/{template}', [AdminGameTemplateController::class, 'update']);
    Route::patch('/templates/{template}/status', [AdminGameTemplateController::class, 'changeStatus']);
    Route::delete('/templates/{template}', [AdminGameTemplateController::class, 'destroy']);

    Route::get('/templates/{template}/objects', [AdminObjectController::class, 'index']);
    Route::post('/templates/{template}/objects', [AdminObjectController::class, 'store']);
    Route::match(['put', 'patch'], '/templates/{template}/objects/{object}', [AdminObjectController::class, 'update']);
    Route::delete('/templates/{template}/objects/{object}', [AdminObjectController::class, 'destroy']);

});
