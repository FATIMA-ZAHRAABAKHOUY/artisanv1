<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LivraisonController;

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('role:client,livreur,admin')->group(function () {
        Route::get('livraisons/{id}',            [LivraisonController::class, 'show']);
        Route::get('livraisons/{id}/historique', [LivraisonController::class, 'historique']);
    });

    Route::middleware('role:livreur,admin')->group(function () {
        Route::get('livraisons/mes-livraisons', [LivraisonController::class, 'mesLivraisons']);
        Route::put('livraisons/{id}/statut',    [LivraisonController::class, 'updateStatut']);
        Route::post('livraisons/{id}/confirmer',[LivraisonController::class, 'confirmer']);
        Route::post('livraisons/{id}/preuve',   [LivraisonController::class, 'uploadPreuve']);
    });
});