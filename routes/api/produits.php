<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProduitController;

Route::get('produits',      [ProduitController::class, 'index']);
Route::get('produits/{id}', [ProduitController::class, 'show']);