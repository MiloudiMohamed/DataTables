<?php

namespace Devmi\Datatables;

use Illuminate\Support\ServiceProvider;

class DataTableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom( __DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__ . '/Views', 'Datatable');

        $this->publishes([
            __DIR__ . '/Views/assets/js/components/' => resource_path('assets/js/vendor/devmi')
        ], 'devmi');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
