<?php

namespace Lintol\Capstone;

use Event;
use Lintol\Capstone\Events\ResultRetrievedEvent;
use Lintol\Capstone\Models\ProcessorConfiguration;
use Lintol\Capstone\Observers\ProcessorConfigurationObserver;
use Lintol\Capstone\Listeners\ResultRetrievedListener;
use Lintol\Capstone\Console\Commands\ObserveDataCommand;
use Lintol\Capstone\Console\Commands\ObserveNewResourcesCommand;
use Lintol\Capstone\Console\Commands\ProcessDataCommand;
use Lintol\Capstone\WampConnection;
use Lintol\Capstone\Models\DataResource;

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
                ObserveNewResourcesCommand::class,
                ProcessDataCommand::class
            ]);
        }

        Event::listen(
            ResultRetrievedEvent::class,
            ResultRetrievedListener::class
        );

        $this->app->singleton(ResourceManager::class, function ($app) {
            return new ResourceManager();
        });

        $this->app->singleton(WampConnection::class, function ($app) {
            $url = config('capstone.wamp.url', 'realm1');
            $realm = config('capstone.wamp.realm', 'realm1');

            return new WampConnection($url, $realm);
        });

        ProcessorConfiguration::observe(ProcessorConfigurationObserver::class);
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
