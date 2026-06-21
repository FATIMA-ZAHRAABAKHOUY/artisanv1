<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\NotificationController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('notifications',          [NotificationController::class, 'index']);
    Route::get('notifications/count',    [NotificationController::class, 'count']);
    Route::put('notifications/{id}/lire',[NotificationController::class, 'marquerLue']);
    Route::put('notifications/lire-tout',[NotificationController::class, 'marquerToutLu']);
});