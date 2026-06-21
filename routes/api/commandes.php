<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CommandeController;

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('role:client,admin')->group(function () {
        Route::get('commandes',     [CommandeController::class, 'index']);
        Route::post('commandes',    [CommandeController::class, 'store']);
        Route::get('commandes/{id}',[CommandeController::class, 'show']);
        Route::post('commandes/{id}/annuler', [CommandeController::class, 'annuler']);
    });

    Route::middleware('role:artisan,admin')->group(function () {
        Route::put('commandes/{id}/statut', [CommandeController::class, 'updateStatut']);
    });
});