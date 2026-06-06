<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices;

use Illuminate\Support\ServiceProvider;

class MpesaInvoicesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mpesa-invoices.php',
            'mpesa-invoices'
        );

        $this->app->singleton('mpesa-invoices', fn () => new InvoiceManager());

        $this->app->alias('mpesa-invoices', InvoiceManager::class);
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/mpesa-invoices.php' => config_path('mpesa-invoices.php'),
        ], 'mpesa-invoices-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'mpesa-invoices-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mpesa-invoices'),
        ], 'mpesa-invoices-views');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mpesa-invoices');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register routes (optional — only when package is installed in a Laravel app)
        if ($this->app->routesAreCached() === false) {
            $this->registerRoutes();
        }
    }

    protected function registerRoutes(): void
    {
        $router = $this->app['router'];

        $router->group([
            'prefix'     => 'invoices',
            'middleware' => ['web', 'auth'],
            'namespace'  => 'FelixMuhoro\MpesaInvoices\Http\Controllers',
        ], function () use ($router) {
            $router->get('{invoiceNumber}/download', 'InvoiceController@download')
                ->name('mpesa-invoices.download');

            $router->post('{invoiceNumber}/email', 'InvoiceController@email')
                ->name('mpesa-invoices.email');
        });
    }
}
