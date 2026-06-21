<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardApiController;
use App\Http\Controllers\API\Admin\UserController;
use App\Http\Controllers\API\CommandeController;
use App\Http\Controllers\API\LivraisonController;
use App\Http\Controllers\API\CategorieController;
use App\Http\Controllers\API\FournisseurController;
use App\Http\Controllers\API\SupportController;
use App\Http\Controllers\API\ArtisanController;

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

    // Dashboard (stats + graphiques via DashboardService)
    Route::get('dashboard', fn () => response()->json([
        'success' => true,
        'data'    => app(\App\Services\DashboardService::class)->getDashboardData()['stats'],
    ]));
    Route::get('dashboard/charts', [AdminDashboardApiController::class, 'charts']);
    Route::get('stats/ventes', fn () => response()->json([
        'success' => true,
        'data'    => app(\App\Repositories\DashboardRepository::class)->charts(),
    ]));
    Route::get('stats/formations', fn () => response()->json([
        'success' => true,
        'data'    => app(\App\Services\DashboardService::class)->getDashboardData()['stats'],
    ]));
    Route::get('stats/artisans', fn () => response()->json([
        'success' => true,
        'data'    => [
            'artisans_actifs' => app(\App\Repositories\DashboardRepository::class)->stats()['artisans_actifs'],
        ],
    ]));

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // Commandes
    Route::get('commandes', [CommandeController::class, 'adminIndex']);
    Route::get('commandes/{id}', [CommandeController::class, 'adminShow']);

    // Livraisons
    Route::get('livraisons', [LivraisonController::class, 'adminIndex']);
    Route::put('livraisons/{id}/assigner', [LivraisonController::class, 'assigner']);

    // Catégories
    Route::post('categories', [CategorieController::class, 'store']);
    Route::put('categories/{id}', [CategorieController::class, 'update']);
    Route::delete('categories/{id}', [CategorieController::class, 'destroy']);

    // Fournisseurs
    Route::post('fournisseurs', [FournisseurController::class, 'store']);
    Route::put('fournisseurs/{id}', [FournisseurController::class, 'update']);
    Route::delete('fournisseurs/{id}', [FournisseurController::class, 'destroy']);

    // Support admin
    Route::get('support', [SupportController::class, 'adminIndex']);
});