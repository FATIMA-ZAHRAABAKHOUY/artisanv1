<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ArtisanController;
use App\Http\Controllers\API\ProduitController;
use App\Http\Controllers\API\FormationController;

Route::get('artisans',      [ArtisanController::class, 'index']);
Route::get('artisans/{id}', [ArtisanController::class, 'show']);

Route::middleware(['auth:sanctum', 'role:artisan', 'artisan.actif'])->group(function () {

    Route::get('artisan/profil', [ArtisanController::class, 'profil']);
    Route::put('artisan/profil', [ArtisanController::class, 'updateProfil']);

    Route::get('artisan/produits',  [ArtisanController::class, 'mesProduits']);
    Route::get('artisan/commandes', [ArtisanController::class, 'mesCommandes']);
    Route::get('artisan/revenus',   [ArtisanController::class, 'mesRevenus']);

    Route::post('produits',      [ProduitController::class, 'store']);
    Route::put('produits/{id}',  [ProduitController::class, 'update']);
    Route::delete('produits/{id}', [ProduitController::class, 'destroy']);
});