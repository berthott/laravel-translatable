<?php

namespace berthott\Translatable;

use berthott\Translatable\Services\TranslatableService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // bind singletons
        $this->app->singleton('Translatable', function () {
            return new TranslatableService();
        });

        // add config
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'translatable');

        $this->extendSchemaMacros();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // publish config
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('translatable.php'),
        ], 'config');

        // load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Extend Schema Macros.
     */
    public function extendSchemaMacros(): void
    {
        Blueprint::macro(
            'translatable',
            function (string $key) {
                $key = $key.'_translatable_content_id';
                $command = $this->unsignedBigInteger($key);
                $this->foreign($key)->references('id')->on('translatable_content')->onDelete('cascade');
                return $command;
            }
        );
    }
}
