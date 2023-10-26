<?php

use Bstepanenko\RoutesDashboard\RoutesDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('routes-dashboard')
    ->name('routes-dashboard.')
    ->group(function () {
        Route::get('/', [RoutesDashboardController::class, 'renderDashboardPage'])->name('dashboard');
});
