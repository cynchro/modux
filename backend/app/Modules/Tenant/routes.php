<?php

use App\Modules\Tenant\Controllers\TenantController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\AdminMiddleware;

$router->group([AuthMiddleware::class, AdminMiddleware::class], function ($router) {
    $router->get('/tenants', [TenantController::class, 'index']);
    $router->post('/tenants', [TenantController::class, 'create']);
    $router->get('/tenants/{id}', [TenantController::class, 'show']);
    $router->put('/tenants/{id}', [TenantController::class, 'update']);
    $router->delete('/tenants/{id}', [TenantController::class, 'delete']);
});
