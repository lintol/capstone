<?php

namespace Lintol\Capstone;

use Event;
use Lintol\Capstone\Events\ResultRetrievedEvent;
use Lintol\Capstone\Listeners\ResultRetrievedListener;
use Lintol\Capstone\Console\Commands\ObserveDataCommand;
use Lintol\Capstone\Console\Commands\ProcessDataCommand;
use Lintol\Capstone\WampConnection;

use Illuminate\Support\ServiceProvider;

class CapstoneServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/capstone.php' => config_path('capstone.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/../examples' => resource_path('capstone/examples')
        ], 'examples');

        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );

        $this->loadTranslationsFrom(
            __DIR__ . '/../translations',
            'capstone'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                ObserveDataCommand::class,
                ProcessDataCommand::class
            ]);
        }

        Event::listen(
            ResultRetrievedEvent::class,
            ResultRetrievedListener::class
        );

        $this->app->singleton(WampConnection::class, function ($app) {
            return new WampConnection;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/capstone.php',
            'capstone'
        );
    }
}
