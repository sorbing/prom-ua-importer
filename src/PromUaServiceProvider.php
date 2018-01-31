<?php

namespace Sorbing\PromUaImporter;

use Illuminate\Support\ServiceProvider;
use Sorbing\PromUaImporter\Console\Commands\ImportProductsCommand;
use Sorbing\PromUaImporter\Console\Commands\ImportOrdersCommand;

class PromUaServiceProvider extends ServiceProvider
{
    protected $defer = true;

    protected function getPackageIdentity()
    {
        return 'prom_ua';
    }

    public function boot()
    {
        $identity = $this->getPackageIdentity();

        $configFile = __DIR__."/config/$identity.php";

        $this->mergeConfigFrom($configFile, "$identity");
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportProductsCommand::class,
                ImportOrdersCommand::class,
            ]);
        }

        $this->publishes([
            $configFile => config_path("$identity.php"),
        ], 'config');

        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    public function provides()
    {
        return ['prom_ua_importer'];
    }

    public function register()
    {
        $this->app->singleton('prom_ua_importer', function ($app) {
            return new \Sorbing\PromUaImporter\Services\PromUaImporter();
        });
    }
}
