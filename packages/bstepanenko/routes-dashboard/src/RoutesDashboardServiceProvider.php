<?php

namespace Bstepanenko\RoutesDashboard;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class RoutesDashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->app->make('Bstepanenko\RoutesDashboard\RoutesDashboardController');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        include __DIR__ . '/routes/web.php';

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'routes-dashboard');

        $this->publishes([
            __DIR__ . '/resources/assets' => public_path('vendor/routes-dashboard'),
        ], 'public');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/routes-dashboard'),
        ]);
    }
}
