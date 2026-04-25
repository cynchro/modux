<?php

namespace App\Modules\Cliente;

use App\Support\ServiceProvider;
use App\Modules\Cliente\Repositories\ClienteRepository;
use App\Modules\Cliente\Services\ClienteService;
use App\Modules\Cliente\Controllers\ClienteController;

class ClienteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(ClienteRepository::class, fn ($c) =>
            new ClienteRepository($c->get(\PDO::class))
        );

        $this->container->singleton(ClienteService::class, fn ($c) =>
            new ClienteService($c->get(ClienteRepository::class))
        );

        $this->container->singleton(ClienteController::class, fn ($c) =>
            new ClienteController($c->get(ClienteService::class))
        );
    }
}