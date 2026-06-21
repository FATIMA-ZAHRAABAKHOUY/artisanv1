<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CategorieController;

Route::get('categories',      [CategorieController::class, 'index']);
Route::get('categories/{id}', [CategorieController::class, 'show']);