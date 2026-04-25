<?php

namespace App\Modules\Auth;

use App\Support\ServiceProvider;
use App\Modules\Auth\Repositories\AuthRepository;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Controllers\AuthController;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(AuthRepository::class, fn ($c) =>
            new AuthRepository($c->get(\PDO::class))
        );

        $this->container->singleton(AuthService::class, fn ($c) =>
            new AuthService($c->get(AuthRepository::class))
        );

        $this->container->singleton(AuthController::class, fn ($c) =>
            new AuthController($c->get(AuthService::class))
        );
    }
}
