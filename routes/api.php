<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\GameTemplateController as AdminGameTemplateController;
use App\Http\Controllers\Api\Admin\ObjectController as AdminObjectController;
use App\Http\Controllers\Api\Admin\Reference\TypeController as AdminTypeController;
use App\Http\Controllers\Api\Admin\Reference\GameFormatController as AdminGameFormatController;
use App\Http\Controllers\Api\Admin\Reference\CardLayoutController as AdminCardLayoutController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\Game\GameTemplateController;
use App\Http\Controllers\Api\Game\ProjectController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\User\ProjectController as UserProjectController;
use App\Http\Controllers\Api\User\ProjectObjectController as UserProjectObjectController;
use App\Http\Controllers\Api\User\ReportController;
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
    Route::patch('/projects/{project}/status', [UserProjectController::class, 'changeStatus']);
    Route::post('/projects/find-similar', [ProjectController::class, 'findSimilar']);
    Route::match(['put', 'patch'], '/projects/{project}', [UserProjectController::class, 'update']);

    Route::get('/projects/{project}/objects', [UserProjectObjectController::class, 'index']);
    Route::post('/projects/{project}/objects', [UserProjectObjectController::class, 'store']);
    Route::match(['put', 'patch'], '/projects/{project}/objects/{object}', [UserProjectObjectController::class, 'update']);
    Route::delete('/projects/{project}/objects/{object}', [UserProjectObjectController::class, 'destroy']);
    Route::post('/projects/{project}/play', [\App\Http\Controllers\Api\User\ProjectController::class, 'recordPlay']);

    Route::get('/types', [\App\Http\Controllers\Api\Admin\Reference\TypeController::class, 'index']);
    Route::get('/formats', [\App\Http\Controllers\Api\Admin\Reference\GameFormatController::class, 'index']);

    Route::get('/community', [\App\Http\Controllers\Api\User\CommunityController::class, 'index']);
    Route::get('/community/{project}', [\App\Http\Controllers\Api\User\CommunityController::class, 'show']);
    Route::post('/community/{project}/duplicate', [\App\Http\Controllers\Api\User\CommunityController::class, 'duplicate']);
    Route::post('/community/{project}/play', [\App\Http\Controllers\Api\User\CommunityController::class, 'recordPlay']);

    Route::post('/ratings', [\App\Http\Controllers\Api\User\RatingController::class, 'store']);
    Route::delete('/ratings/{rating}', [\App\Http\Controllers\Api\User\RatingController::class, 'destroy']);
    Route::delete('/ratings', [\App\Http\Controllers\Api\User\RatingController::class, 'clear']);

    Route::get('/templates/{template}/comments', [\App\Http\Controllers\Api\User\CommentController::class, 'indexForTemplate']);
    Route::get('/projects/{project}/comments', [\App\Http\Controllers\Api\User\CommentController::class, 'indexForProject']);
    Route::post('/comments', [\App\Http\Controllers\Api\User\CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [\App\Http\Controllers\Api\User\CommentController::class, 'destroy']);

    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

});

Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);

// Mot de passe oublié (public)
Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp']);
Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

//restauration du compte
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

    Route::get('/types', [AdminTypeController::class, 'index']);
    Route::post('/types', [AdminTypeController::class, 'store']);
    Route::match(['put', 'patch'], '/types/{type}', [AdminTypeController::class, 'update']);
    Route::delete('/types/{type}', [AdminTypeController::class, 'destroy']);
    Route::post('/types/{id}/restore', [AdminTypeController::class, 'restore']);

    Route::get('/formats', [AdminGameFormatController::class, 'index']);
    Route::post('/formats', [AdminGameFormatController::class, 'store']);
    Route::match(['put', 'patch'], '/formats/{format}', [AdminGameFormatController::class, 'update']);
    Route::delete('/formats/{format}', [AdminGameFormatController::class, 'destroy']);
    Route::post('/formats/{id}/restore', [AdminGameFormatController::class, 'restore']);

    Route::get('/card-layouts', [AdminCardLayoutController::class, 'index']);

    Route::get('/projects', [\App\Http\Controllers\Api\Admin\ModerationController::class, 'index']);
    Route::post('/projects/{project}/moderate', [\App\Http\Controllers\Api\Admin\ModerationController::class, 'moderate']);

    Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/{report}', [AdminReportController::class, 'show'])->name('admin.reports.show');
    Route::patch('/reports/{report}', [AdminReportController::class, 'update'])->name('admin.reports.update');
});
