<?php

use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('analytics-module')->group(function () {
    Route::get('/health', function () {
        $status = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'memory_usage' => memory_get_usage(),
            'memory_limit' => ini_get('memory_limit'),
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        ];

        return response()->json($status);
    });

    Route::apiResource('logs', LogController::class);
    
    Route::get('logs/content/{contentId}', [LogController::class, 'contentLogs']);
    Route::get('logs/session/{sessionId}', [LogController::class, 'sessionLogs']);
    Route::get('logs/content/{contentId}/statistics', [LogController::class, 'contentStatistics']);
});
