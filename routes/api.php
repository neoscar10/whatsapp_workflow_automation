<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'index'])->name('index');
                Route::post('/sync', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'sync'])->name('sync');
                Route::get('/{id}', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'show'])->name('show');
                Route::delete('/{id}', [\App\Http\Controllers\Api\V1\WhatsApp\WhatsAppTemplateController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
