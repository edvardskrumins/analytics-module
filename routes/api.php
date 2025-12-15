<?php

use Illuminate\Support\Facades\Route;

Route::prefix('analytics')->group(function () {
    Route::get('/health', function () {
      return response()->json([
          'module' => 'analytics-module',
          'message' => 'Analytics module routes are loaded successfully!',
      ]);
    });
});
