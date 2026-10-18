<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UtilisateurController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function (): void {
    Route::post('/login', 'login')->middleware('throttle:5,1');
    Route::post('/logout', 'logout')->middleware(['auth:sanctum']);
    Route::get('/me', 'me')->middleware(['auth:sanctum']);
});

Route::controller(UtilisateurController::class)->prefix('utilisateurs')->group(function (): void {
    Route::post('/', 'store')->middleware(['auth:sanctum', 'role:ADMIN,SCOLARITE']);
    Route::get('/', 'index')->middleware(['auth:sanctum', 'role:ADMIN']);
    Route::patch('/{id}/desactiver', 'desactiver')->middleware(['auth:sanctum', 'role:ADMIN']);
});
