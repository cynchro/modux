<?php

use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Admin\Controllers\LogsController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\TenantMiddleware;

// Global admin routes — not tenant-scoped (roles, permisos, and logs are shared)
$router->group([AuthMiddleware::class, AdminMiddleware::class], function ($router) {
    // Roles
    $router->get('/admin/roles', [AdminController::class, 'indexRoles']);
    $router->post('/admin/roles', [AdminController::class, 'storeRole']);
    $router->get('/admin/roles/{id}', [AdminController::class, 'showRole']);
    $router->put('/admin/roles/{id}', [AdminController::class, 'updateRole']);
    $router->post('/admin/roles/{id}/assign', [AdminController::class, 'assignPermisos']);
    $router->delete('/admin/roles/{id}/assign', [AdminController::class, 'unassignPermisos']);

    // Permisos
    $router->get('/admin/permisos', [AdminController::class, 'indexPermisos']);
    $router->post('/admin/permisos', [AdminController::class, 'storePermiso']);
    $router->get('/admin/permisos/{id}', [AdminController::class, 'showPermiso']);
    $router->put('/admin/permisos/{id}', [AdminController::class, 'updatePermiso']);

    // Logs
    $router->get('/admin/logs', [LogsController::class, 'index']);
    $router->get('/admin/logs/{id}', [LogsController::class, 'show']);
    $router->delete('/admin/logs', [LogsController::class, 'deleteAll']);
});

// Tenant-scoped admin routes — users are isolated per tenant
$router->group([AuthMiddleware::class, AdminMiddleware::class, TenantMiddleware::class], function ($router) {
    $router->get('/admin/users', [AdminController::class, 'users']);
    $router->post('/admin/impersonate', [AdminController::class, 'impersonate']);
});
