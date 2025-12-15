<?php

namespace TetOtt\AnalyticsModule;

use Illuminate\Support\ServiceProvider;

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

         $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}

