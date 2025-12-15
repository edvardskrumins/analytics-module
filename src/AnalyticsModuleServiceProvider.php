<?php

namespace TetOtt\AnalyticsModule;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
/**
 * Analytics Module Service Provider
 */
class AnalyticsModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        Route::prefix('api')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }
}

