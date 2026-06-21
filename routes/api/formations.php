<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FormationController;

Route::get('formations',      [FormationController::class, 'index']);
Route::get('formations/{id}', [FormationController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('role:apprenant,client,admin')->group(function () {
        Route::post('formations/{id}/inscrire', [FormationController::class, 'inscrire']);
        Route::get('formations/{id}/ressources', [FormationController::class, 'ressources']);
        Route::get('formations/mes-inscriptions',[FormationController::class, 'mesInscriptions']);
    });

    Route::middleware('role:artisan,admin')->group(function () {
        Route::post('formations',      [FormationController::class, 'store']);
        Route::put('formations/{id}',  [FormationController::class, 'update']);
        Route::delete('formations/{id}', [FormationController::class, 'destroy']);
    });
});