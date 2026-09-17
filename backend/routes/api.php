<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Converter\ConvertController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Parser\CompanyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;


Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::middleware(['auth:sanctum', 'verified', 'check.status'])->group(function () {

    Route::get('/chats/{chat}/sync', [ChatController::class, 'sync']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/companies', [CompanyController::class, 'store'])->name('company.store');
    Route::post('/convert', ConvertController::class);

    Route::middleware('role:admin')->group(function (){
        Route::get('/users', [UserController::class,'index']);
        Route::put('/users/{id}', [UserController::class, 'update']);
    });

    Route::post('/chats/{id}/clear', [ChatController::class, 'clearMessages']);
    Route::delete('/chats/{id}', [ChatController::class, 'destroy']);
    Route::get('/chats', [ChatController::class, 'getChats']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/users', [ChatController::class, 'getUsers']);
    Route::post('/chats/group', [ChatController::class, 'createGroup']);
    Route::delete('/chats/{chat}/users/{user}', [ChatController::class, 'removeUser']);


    Route::post('/chats/{id}/read', [MessageController::class, 'markAsRead']);
    Route::get('/chats/{id}/messages', [MessageController::class, 'getMessages']);
    Route::post('/chats/{id}/messages', [MessageController::class, 'sendMessage']);
    Route::put('/messages/{message}', [MessageController::class, 'update']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);

    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

    Route::post('/messages/{message}/reactions', [MessageController::class, 'toggleReaction']);

    Route::get('/chats/{chatId}/context/{messageId}', [MessageController::class, 'getContextMessage']);

});

//Route::middleware('auth:sanctum')->group(function () {

//Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
// Включение и выключение двухфакторной аутентификации (2FA)
//Route::post('/user/two-factor-authentication', \Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController::class . '@store');
//Route::delete('/user/two-factor-authentication', \Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController::class . '@destroy');

// Получение QR-кода и секретного текстового ключа для Vue
//Route::get('/user/two-factor-qr-code', \Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController::class . '@show');
//Route::get('/user/two-factor-secret-key', \Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController::class . '@show');

// Работа с резервными кодами восстановления (Тут исправленный RecoveryCodeController)
//Route::get('/user/two-factor-recovery-codes', \Laravel\Fortify\Http\Controllers\RecoveryCodeController::class . '@index');
//Route::post('/user/two-factor-recovery-codes', \Laravel\Fortify\Http\Controllers\RecoveryCodeController::class . '@store');

// Подтверждение корректности первого ввода кода из Google Authenticator
//Route::post('/user/confirmed-two-factor-authentication', \Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController::class . '@store');

//});


