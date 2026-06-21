<?php
use App\Http\Controllers\API\InscriptionFormationController;


// =======================================================
// routes/api.php (ENTRY POINT)
// Charge toutes les routes API du projet
// =======================================================

// Auth
require __DIR__.'/api/auth.php';

// Produits
require __DIR__.'/api/produits.php';

// Catégories
require __DIR__.'/api/categories.php';

// Commandes
require __DIR__.'/api/commandes.php';

// Paiements
require __DIR__.'/api/paiements.php';

// Livraisons
require __DIR__.'/api/livraisons.php';

// Formations
require __DIR__.'/api/formations.php';

// Artisan
require __DIR__.'/api/artisan.php';

// Fournisseurs
require __DIR__.'/api/fournisseurs.php';

// Notifications
require __DIR__.'/api/notifications.php';

// Support
require __DIR__.'/api/support.php';

// Admin
require __DIR__.'/api/admin.php';


Route::middleware('auth:sanctum')->group(function () {
    // Apprenant / Client
    Route::middleware('role:apprenant,client,admin')->group(function () {
        Route::get ('formations/mes-inscriptions',                [InscriptionFormationController::class, 'mesInscriptions']);
        Route::get ('formations/inscriptions/{id}',               [InscriptionFormationController::class, 'show']);
        Route::post('formations/{id}/inscrire',                   [InscriptionFormationController::class, 'inscrire']);
        Route::put ('formations/inscriptions/{id}/abandonner',    [InscriptionFormationController::class, 'abandonner']);
    });

    // Artisan + Admin
    Route::middleware('role:artisan,admin')->group(function () {
        Route::put ('formations/inscriptions/{id}/progression',   [InscriptionFormationController::class, 'updateProgression']);
        Route::get ('formations/{id}/inscrits',                   [InscriptionFormationController::class, 'inscrits']);
        Route::post('formations/inscriptions/{id}/certificat',    [InscriptionFormationController::class, 'delivrerCertificat']);
    });

    // Admin uniquement
        Route::middleware('role:admin')->group(function () {
        Route::get('admin/inscriptions',                          [InscriptionFormationController::class, 'adminIndex']);
        Route::put('formations/inscriptions/{id}/suspendre',      [InscriptionFormationController::class, 'suspendre']);
    });
});

