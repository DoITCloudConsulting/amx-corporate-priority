<?php

namespace Amx\CorporatePriority\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class CorporatePriorityServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        Inertia::share('version', function () {
            return '1.0.0';
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/corporate-priority.php', 'corporate-priority');
    }
}
