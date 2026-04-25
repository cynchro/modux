<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// ── Stage 1: Environment ──────────────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$dotenv->required(['JWT_SECRET', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

// ── Stage 2: Config ───────────────────────────────────────────────────────────
App\Support\Config::setPath(dirname(__DIR__) . '/config');

// ── Stage 3: Error display ────────────────────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '0');

// ── Stage 4: Container ────────────────────────────────────────────────────────
$app = new App\Support\Container();

// ── Stage 5: Logger & exception handler ──────────────────────────────────────
$app->singleton(App\Support\Logger::class, fn () =>
    new App\Support\Logger(App\Support\Config::all('logging')));

App\Exceptions\Handler::register($app->get(App\Support\Logger::class));

// ── Stage 6: Database ─────────────────────────────────────────────────────────
$app->singleton(\PDO::class, function (): \PDO {
    $cfg = App\Support\Config::all('database');
    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
    return new \PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
});

// Backward compat: keep Database::getConnection() working for legacy repositories
App\Config\Database::setConnection($app->get(\PDO::class));

// ── Stage 7: Router & Kernel ──────────────────────────────────────────────────
$app->singleton(App\Support\Router::class, fn ($c) =>
    new App\Support\Router($c));

$app->singleton(App\Support\Kernel::class, fn ($c) =>
    new App\Support\Kernel($c));

// ── Stage 8: Module service providers ────────────────────────────────────────
$providers = [
    App\Modules\Auth\AuthServiceProvider::class,
    App\Modules\Usuario\UsuarioServiceProvider::class,
    App\Modules\Admin\AdminServiceProvider::class,
    App\Modules\Cliente\ClienteServiceProvider::class,
    App\Modules\Tenant\TenantServiceProvider::class,
];

$providerInstances = [];
foreach ($providers as $providerClass) {
    $instance = new $providerClass($app);
    $instance->register();
    $providerInstances[] = $instance;
}

foreach ($providerInstances as $instance) {
    $instance->boot();
}

// ── Stage 9: Infrastructure routes (not module concerns) ─────────────────────
$app->singleton(App\Http\Controllers\HealthController::class, fn ($c) =>
    new App\Http\Controllers\HealthController($c->get(\PDO::class)));

$app->get(App\Support\Router::class)
    ->get('/health', [App\Http\Controllers\HealthController::class, 'check']);

return $app;
