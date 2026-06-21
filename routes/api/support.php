<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SupportController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('support',     [SupportController::class, 'index']);
    Route::post('support',    [SupportController::class, 'store']);
    Route::get('support/{id}',[SupportController::class, 'show']);
});