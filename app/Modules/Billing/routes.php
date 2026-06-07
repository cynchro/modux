<?php

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\TenantMiddleware;
use App\Modules\Billing\Controllers\BillingController;

// Módulo opcional: solo se activa si el SDK de billing está instalado.
if (!class_exists(\Cynchro\Billing\BillingManager::class)) {
    return;
}

// Checkout: requiere usuario autenticado y tenant.
$router->group([AuthMiddleware::class, TenantMiddleware::class], function ($router) {
    $router->post('/billing/checkout', [BillingController::class, 'checkout']);
});

// Webhook de la pasarela: público (las pasarelas no envían JWT); se valida por firma.
$router->post('/billing/webhook/{gateway}', [BillingController::class, 'webhook']);
