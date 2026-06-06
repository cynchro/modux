<?php

use App\Modules\IA\Controllers\IAController;
use App\Http\Middleware\AuthMiddleware;

$router->group([AuthMiddleware::class], '/ia', function ($router) {
    $router->post('/chat',     [IAController::class, 'chat']);
    $router->post('/ask',      [IAController::class, 'ask']);
    $router->post('/ingest',   [IAController::class, 'ingest']);
    $router->post('/retrieve', [IAController::class, 'retrieve']);
});
