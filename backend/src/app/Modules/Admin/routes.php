<?php

use App\Modules\Admin\Controllers\LogsController;
use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Admin\Middleware\AdminMiddleware;

$router->get('/admin', [AdminController::class, 'home']);

$router->get('/admin/roles', [AdminController::class, 'roles']);
$router->get('/admin/roles/abm/{id}', [AdminController::class, 'rolesABM']);
$router->post('/admin/roles/createOrUpdate', [AdminController::class, 'gestionarRoles']);



$router->get('/admin/permisos', [AdminController::class, 'permisos']);
$router->get('/admin/permisos/abm/{id}', [AdminController::class, 'permisosABM']);
$router->post('/admin/permisos/createOrUpdate', [AdminController::class, 'gestionarPermisos']);

$router->get('/admin/logs', [LogsController::class, 'index']);
$router->get('/admin/logs/show/{id}', [LogsController::class, 'show']);
$router->post('/admin/logs', [LogsController::class, 'delete']);

$router->get('/admin/impersonalizar', [AdminController::class, 'impersonalizar']);
$router->post('/admin/impersonalizar', [AdminController::class, 'loguear']);

