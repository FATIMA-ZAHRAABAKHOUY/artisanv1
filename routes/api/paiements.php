<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PaiementController;

Route::middleware('auth:sanctum')->group(function () {

    Route::post('paiements/{commande_id}', [PaiementController::class, 'payer']);
    Route::get('paiements/{id}',          [PaiementController::class, 'show']);
});

Route::post('paiements/webhook', [PaiementController::class, 'webhook']);