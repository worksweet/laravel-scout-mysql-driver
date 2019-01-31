<?php

namespace Yab\MySQLScout\Providers;

use Yab\MySQLScout\Engines\Modes\ModeContainer;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Yab\MySQLScout\Engines\MySQLEngine;
use Yab\MySQLScout\Services\ModelService;
use Yab\MySQLScout\Services\IndexService;
use Yab\MySQLScout\Commands\ManageIndexes;

class MySQLScoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ManageIndexes::class,
            ]);
        }

        $this->app->make(EngineManager::class)->extend('mysql', function () {
            return new MySQLEngine(app(ModeContainer::class));
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(ModelService::class, function ($app) {
            return new ModelService();
        });

        $this->app->singleton(IndexService::class, function ($app) {
            return new IndexService($app->make(ModelService::class));
        });

        $this->app->singleton(ModeContainer::class, function ($app) {
            $modeClass = config('scout.mysql.mode');
            $fallbackModeClass = config('scout.mysql.min_fulltext_search_fallback');

            return new ModeContainer(app($modeClass), app($fallbackModeClass));
        });
    }
}
