<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FournisseurController;

Route::get('fournisseurs',      [FournisseurController::class, 'index']);
Route::get('fournisseurs/{id}', [FournisseurController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('fournisseurs',      [FournisseurController::class, 'store'])->middleware('role:admin');
    Route::put('fournisseurs/{id}',  [FournisseurController::class, 'update'])->middleware('role:admin');
    Route::delete('fournisseurs/{id}', [FournisseurController::class, 'destroy'])->middleware('role:admin');
});