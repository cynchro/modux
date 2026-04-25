<?php

namespace App\Modules\Logs;

use App\Support\ServiceProvider;
use App\Modules\Logs\Services\LogService;
use App\Modules\Logs\Controllers\LogsController;

class LogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(LogService::class, fn () =>
            new LogService()
        );

        $this->container->singleton(LogsController::class, fn ($c) =>
            new LogsController($c->get(LogService::class))
        );
    }
}
