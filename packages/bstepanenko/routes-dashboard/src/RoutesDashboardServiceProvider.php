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
        $this->app->make('Bstepanenko\RoutesDashboard\RouteDashboardController');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        include __DIR__ . '/routes/web.php';
    }
}
