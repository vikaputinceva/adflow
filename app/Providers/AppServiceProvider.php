<?php

namespace App\Providers;

use App\Models\Campaign;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // в боковое меню передаём общий бюджет, чтобы не считать его в каждом контроллере
        View::composer('partials.sidebar', function ($view) {
            $view->with('budgetTotals', Campaign::totals());
        });
    }
}
