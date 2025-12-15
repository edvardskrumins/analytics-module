<?php

use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Route;

Route::prefix('analytics-module')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'module' => 'analytics-module',
            'message' => 'Analytics module routes are loaded successfully!',
        ]);
    });

    Route::apiResource('logs', LogController::class);
    
    Route::get('logs/content/{contentId}', [LogController::class, 'contentLogs']);
    Route::get('logs/session/{sessionId}', [LogController::class, 'sessionLogs']);
    Route::get('logs/content/{contentId}/statistics', [LogController::class, 'contentStatistics']);
});
