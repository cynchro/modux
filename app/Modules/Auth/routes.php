<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Http\Middleware\AuthMiddleware;

$router->post('/auth/register',    [AuthController::class, 'register']);
$router->post('/auth/login',       [AuthController::class, 'login']);
$router->post('/auth/logout',      [AuthController::class, 'logout'],     [AuthMiddleware::class]);
$router->post('/auth/me',          [AuthController::class, 'me'],          [AuthMiddleware::class]);
$router->get('/auth/permisos/{key}', [AuthController::class, 'permisos'], [AuthMiddleware::class]);
$router->post('/auth/impersonate', [AuthController::class, 'impersonate'], [AuthMiddleware::class]);
