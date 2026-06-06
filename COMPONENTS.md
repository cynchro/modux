# Modux — Guía para construir nuevos componentes

Comparte este documento con tu IA cuando le pidas construir cualquier componente nuevo para este framework.

---

## Qué es Modux

PHP 8.2 modular monolith. Filosofía: **sin magia, sin autoload mágico, sin dependencias externas**. Todo es explícito. El framework provee routing, PSR-11 DI, middleware pipeline, JWT auth, multi-tenancy, RBAC, events, service providers, transactions, cache, job queue y migraciones.

Raíz del código fuente: `backend/src/`

---

## Las dos categorías de componente

### 1. Módulo de negocio (`app/Modules/{Name}/`)

Para: Cliente, Factura, Producto, Webhook, Notificación, etc. — cualquier dominio que pertenezca al producto.

**Auto-descubierto**: el bootstrap hace glob de `app/Modules/*/routes.php`. No requiere registro manual.

Estructura obligatoria:
```
app/Modules/{Name}/
├── Controllers/
│   └── {Name}Controller.php
├── Repositories/
│   └── {Name}Repository.php
├── Requests/
│   └── Create{Name}Request.php
│   └── Update{Name}Request.php
├── Services/
│   └── {Name}Service.php
└── routes.php
```

### 2. Infraestructura (`app/Http/` + `app/Support/`)

Para: panel admin visual, queue monitor, métricas, feature flags, health checks, etc. — cualquier cosa que sea del sistema, no del negocio.

**Registro explícito**: las rutas van en `bootstrap/app.php` Stage 9. El controller va en `app/Http/Controllers/`. El service/soporte va en `app/Support/`.

> **Regla crítica**: NUNCA pongas infraestructura en `app/Modules/`. NUNCA pongas dominio de negocio en `app/Http/`.

---

## Generador CLI

```bash
php modux make:module <Name>             # genera módulo completo
php modux make:module <Name> --with-tenant  # agrega TenantMiddleware + repositorio con tenant_id
```

Para infraestructura: crea los archivos a mano, no hay generador.

---

## Primitivas disponibles — úsalas, no las reinventes

### Container (PSR-11)

```php
$container->get(SomeClass::class);          // autowire por reflexión
$container->makeWith(SomeClass::class, 'param1', 42);  // inyecta escalares posicionalmente
$container->bind(InterfaceClass::class, fn($c) => new ConcreteClass($c->get(PDO::class)));
$container->singleton(SomeClass::class, fn($c) => new SomeClass());
```

### Router

```php
$router->get('/path', [Controller::class, 'method']);
$router->post('/path', [Controller::class, 'method'], [AuthMiddleware::class]);
$router->group([AuthMiddleware::class, TenantMiddleware::class], '/prefix', function ($router) {
    $router->get('/items', [ItemController::class, 'index']);
    $router->post('/items', [ItemController::class, 'create']);
});
// Middleware con parámetro:
$router->delete('/invoices/{id}', [InvoiceController::class, 'delete'], [
    AuthMiddleware::class,
    PermissionMiddleware::class . ':facturas.delete',
]);
```

### Request / Response

```php
// En el controller:
public function create(Request $request, CreateItemRequest $validated): Response
{
    $tenantId = (string) $request->tenantId();   // seteado por TenantMiddleware
    $userId   = $request->user()['id'];           // seteado por AuthMiddleware
    return Response::success($data, 201);
    return Response::error('Not found', 404);
    return Response::json(['key' => 'value'], 200);
}
// FormRequest — validación automática al construirse:
public function rules(): array { return ['name' => ['required', 'string', 'max:255']]; }
```

### DB + Transacciones

```php
// Inyectar PDO directamente en repositories
public function __construct(private \PDO $pdo) {}

// Transacciones (inyectar DB)
public function __construct(private DB $db) {}
$result = $this->db->withTransaction(function () use ($data) {
    $this->repoA->insert($data);
    $this->repoB->update($data['id']);
    return $data;
});
```

### Auth y Roles

```php
// Middleware en rutas:
AuthMiddleware::class                   // requiere JWT válido
AdminMiddleware::class                  // requiere rol ADMIN
PermissionMiddleware::class . ':key'    // requiere permiso específico

// Constantes:
use App\Support\Roles;
if ($user['rol'] === Roles::ADMIN) { ... }   // ADMIN = 1, USER = 0
```

### Multi-tenancy

```php
// Agregar TenantMiddleware a las rutas
TenantMiddleware::class   // lee tenant_id del JWT, verifica en DB, setea $request->tenantId()

// En el repository, scopear queries:
public function findAll(string $tenantId): array
{
    $stmt = $this->pdo->prepare('SELECT * FROM items WHERE tenant_id = ?');
    $stmt->execute([$tenantId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
```

### Cache

```php
// Inyectar CacheInterface (auto-wired a ApcuCache en prod, ArrayCache en tests)
public function __construct(private CacheInterface $cache) {}

$this->cache->get('key');
$this->cache->set('key', $value, ttl: 300);
$this->cache->delete('key');
$this->cache->has('key');
```

### Events

```php
public function __construct(private EventDispatcher $dispatcher) {}

$this->dispatcher->listen('user.created', function (array $payload) {
    // reaccionar al evento
});
$this->dispatcher->dispatch('user.created', ['id' => $userId, 'email' => $email]);
$this->dispatcher->hasListeners('user.created');
```

Los listeners se registran en el `boot()` de un ServiceProvider o directo en el service.

### ServiceProvider (para módulos que necesitan inicialización)

```php
namespace App\Modules\Webhook;

use App\Support\ServiceProvider;
use App\Support\Container;
use App\Support\EventDispatcher;

class WebhookServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        // bindings opcionales
    }

    public function boot(Container $container): void
    {
        $dispatcher = $container->get(EventDispatcher::class);
        $service    = $container->get(WebhookService::class);

        $dispatcher->listen('user.created', fn($p) => $service->trigger('user.created', $p));
    }
}
```

El bootstrap auto-descubre `app/Modules/*/ServiceProvider.php`. Llama `register()` en todos primero, luego `boot()` en todos.

### Job Queue

```php
// Definir un job:
class SendWebhookJob extends Job
{
    public string $url     = '';
    public string $payload = '';
    // Solo propiedades públicas (excepto queue/maxAttempts/delaySeconds) se serializan

    public function handle(Container $container): void
    {
        $container->get(HttpClient::class)->post($this->url, $this->payload);
    }
}

// Despachar:
public function __construct(private JobDispatcher $dispatcher) {}
$job          = new SendWebhookJob();
$job->url     = 'https://example.com/hook';
$job->payload = json_encode($data);
$job->queue   = 'webhooks';       // opcional, default: 'default'
$job->delaySeconds = 5;           // opcional
$this->dispatcher->dispatch($job);

// Worker CLI:
// php modux queue:work --queue=webhooks --sleep=3
```

### Rate Limiter

```php
public function __construct(private RateLimiter $rateLimiter) {}

$key = 'action:' . $userId;
if ($this->rateLimiter->tooManyAttempts($key)) {
    throw new RateLimitException('Too many attempts.');
}
$this->rateLimiter->hit($key, ttlSeconds: 300);
$this->rateLimiter->clear($key);
```

### Paginación

```php
use App\Helpers\PaginatorHelper;

// $query DEBE ser string estático. Valores dinámicos van en $params.
$result = PaginatorHelper::paginate(
    pdo:      $this->pdo,
    query:    'SELECT * FROM items WHERE tenant_id = ? AND status = ?',
    params:   [$tenantId, 'active'],
    page:     (int) ($queryParams['page'] ?? 1),
    perPage:  (int) ($queryParams['per_page'] ?? 20),
);
// Retorna: { total, cantidad_por_pagina, pagina, cantidad_total, results }
```

### Excepciones (todas se convierten a JSON automáticamente)

```php
throw new AuthException('Token inválido.');          // 401
throw new ForbiddenException('Sin permisos.');       // 403
throw new NotFoundException('Item', $id);            // 404
throw new ValidationException(['field' => ['msg']]); // 422
throw new RateLimitException('Demasiados intentos'); // 429
throw new DatabaseException('Query fallida');        // 500
```

### Config

```php
Config::get('auth.jwt_secret');
Config::get('app.debug', false);
Config::all('database');
```

Archivos en `config/` (app.php, auth.php, cors.php, database.php, logging.php, mail.php).

### Logger (PSR-3)

```php
public function __construct(private Logger $logger) {}
$this->logger->info('Webhook enviado', ['url' => $url, 'status' => 200]);
$this->logger->error('Webhook falló', ['error' => $e->getMessage()]);
```

### UUID

```php
use App\Support\UUIDGenerator;
$id = UUIDGenerator::v4();
```

---

## Migraciones

Archivo: `migrations/00NN_descripcion.php`

```php
<?php
return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE webhooks (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id   CHAR(36)     NOT NULL,
            url         VARCHAR(500) NOT NULL,
            secret      VARCHAR(255) NOT NULL,
            events      JSON         NOT NULL,
            active      TINYINT(1)   NOT NULL DEFAULT 1,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS webhooks');
    }
};
```

```bash
php modux migrate           # aplica pendientes
php modux migrate:rollback  # deshace último batch
php modux migrate:fresh     # rollback todo + re-aplica
```

---

## Reglas de validación disponibles

`required`, `email`, `min:N`, `max:N`, `integer`, `numeric`, `boolean`, `string`, `array`, `in:a,b,c`, `url`, `date`, `date:format`, `regex:/pattern/`, `uuid`, `confirmed`, `nullable`

---

## Convenciones de testing

### Unit test (sin DB)

```php
namespace Tests\Unit\Modules\Webhook;

use Tests\Unit\UnitTestCase;

class WebhookServiceTest extends UnitTestCase
{
    public function test_dispatch_calls_job_dispatcher(): void
    {
        $dispatcher = $this->createMock(JobDispatcher::class);
        $dispatcher->expects($this->once())->method('dispatch');
        // ...
    }
}
```

`UnitTestCase::makeRequest(?array $user, ?string $tenantId): Request` — construye request con user/tenant seteados.

### Feature test (con DB real, rollback automático)

```php
namespace Tests\Feature\Modules\Webhook;

use Tests\Feature\FeatureTestCase;

class WebhookTest extends FeatureTestCase
{
    public function test_create_webhook_returns_201(): void
    {
        Request::setTestInputStream(json_encode(['url' => 'https://example.com/hook', 'events' => ['user.created']]));
        $response = $this->post('/webhooks', headers: $this->authHeader());
        $this->assertSame(201, $response->getStatusCode());
    }
}
```

`FeatureTestCase` provee: `post()`, `get()`, `put()`, `delete()`, acceso a PDO, rollback automático por test.

Fixtures de jobs/clases auxiliares para tests: `tests/Unit/{Module}/Fixtures/`.

---

## Checklist de calidad (obligatorio antes de dar por terminado)

```bash
cd backend/src
composer test      # debe pasar todos los tests (actualmente 152+)
composer lint      # PSR-12, cero errores (snake_case en tests está excluido)
composer analyse   # phpstan level 6, cero errores
```

Si algún comando falla, hay que resolverlo. No se entregan componentes con errores de lint o análisis estático.

---

## Ejemplo completo: módulo Webhooks

### 1. Migración `migrations/0007_create_webhooks_table.php`
```php
<?php
return new class {
    public function up(\PDO $pdo): void {
        $pdo->exec('CREATE TABLE webhooks (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id  CHAR(36)     NOT NULL,
            url        VARCHAR(500) NOT NULL,
            secret     VARCHAR(255) NOT NULL,
            events     JSON         NOT NULL,
            active     TINYINT(1)   NOT NULL DEFAULT 1,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }
    public function down(\PDO $pdo): void { $pdo->exec('DROP TABLE IF EXISTS webhooks'); }
};
```

### 2. `app/Modules/Webhook/Requests/CreateWebhookRequest.php`
```php
<?php
namespace App\Modules\Webhook\Requests;
use App\Support\FormRequest;
class CreateWebhookRequest extends FormRequest {
    public function rules(): array {
        return ['url' => ['required', 'url'], 'events' => ['required', 'array']];
    }
}
```

### 3. `app/Modules/Webhook/Repositories/WebhookRepository.php`
```php
<?php
namespace App\Modules\Webhook\Repositories;
class WebhookRepository {
    public function __construct(private \PDO $pdo) {}

    public function create(array $data, string $tenantId): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO webhooks (tenant_id, url, secret, events) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$tenantId, $data['url'], $data['secret'], json_encode($data['events'])]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findActive(string $tenantId, string $event): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM webhooks WHERE tenant_id = ? AND active = 1 AND JSON_CONTAINS(events, ?)'
        );
        $stmt->execute([$tenantId, json_encode($event)]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### 4. `app/Modules/Webhook/Services/WebhookService.php`
```php
<?php
namespace App\Modules\Webhook\Services;
use App\Modules\Webhook\Repositories\WebhookRepository;
use App\Support\JobDispatcher;
use App\Modules\Webhook\Jobs\DeliverWebhookJob;
class WebhookService {
    public function __construct(
        private WebhookRepository $repo,
        private JobDispatcher $dispatcher,
    ) {}

    public function create(array $data, string $tenantId): int {
        $data['secret'] = bin2hex(random_bytes(16));
        return $this->repo->create($data, $tenantId);
    }

    public function trigger(string $event, array $payload, string $tenantId): void {
        foreach ($this->repo->findActive($tenantId, $event) as $hook) {
            $job          = new DeliverWebhookJob();
            $job->url     = $hook['url'];
            $job->secret  = $hook['secret'];
            $job->payload = json_encode($payload);
            $job->queue   = 'webhooks';
            $this->dispatcher->dispatch($job);
        }
    }
}
```

### 5. `app/Modules/Webhook/Controllers/WebhookController.php`
```php
<?php
namespace App\Modules\Webhook\Controllers;
use App\Modules\Webhook\Requests\CreateWebhookRequest;
use App\Modules\Webhook\Services\WebhookService;
use App\Support\Request;
use App\Support\Response;
class WebhookController {
    public function __construct(private WebhookService $service) {}

    public function create(Request $request, CreateWebhookRequest $validated): Response {
        $id = $this->service->create($validated->validated(), (string) $request->tenantId());
        return Response::success(['id' => $id], 201);
    }
}
```

### 6. `app/Modules/Webhook/routes.php`
```php
<?php
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\TenantMiddleware;
use App\Modules\Webhook\Controllers\WebhookController;

$router->group([AuthMiddleware::class, TenantMiddleware::class], '/webhooks', function ($router) {
    $router->post('', [WebhookController::class, 'create']);
});
```

### 7. `app/Modules/Webhook/WebhookServiceProvider.php`
```php
<?php
namespace App\Modules\Webhook;
use App\Support\ServiceProvider;
use App\Support\Container;
use App\Support\EventDispatcher;
use App\Modules\Webhook\Services\WebhookService;
class WebhookServiceProvider extends ServiceProvider {
    public function boot(Container $container): void {
        $dispatcher = $container->get(EventDispatcher::class);
        $service    = $container->get(WebhookService::class);
        $dispatcher->listen('user.created', fn($p) => $service->trigger('user.created', $p['data'], $p['tenant_id']));
    }
}
```

### 8. `tests/Unit/Modules/Webhook/WebhookServiceTest.php`
```php
<?php
namespace Tests\Unit\Modules\Webhook;
use App\Modules\Webhook\Repositories\WebhookRepository;
use App\Modules\Webhook\Services\WebhookService;
use App\Support\JobDispatcher;
use Tests\Unit\UnitTestCase;
class WebhookServiceTest extends UnitTestCase {
    public function test_trigger_dispatches_job_for_each_active_hook(): void {
        $repo       = $this->createMock(WebhookRepository::class);
        $dispatcher = $this->createMock(JobDispatcher::class);
        $repo->method('findActive')->willReturn([
            ['url' => 'https://a.com', 'secret' => 'abc'],
            ['url' => 'https://b.com', 'secret' => 'xyz'],
        ]);
        $dispatcher->expects($this->exactly(2))->method('dispatch');
        (new WebhookService($repo, $dispatcher))->trigger('user.created', ['id' => 1], 'tenant-1');
    }
}
```

---

## Lo que NO debes hacer

- No pongas infraestructura en `app/Modules/`. No pongas negocio en `app/Http/`.
- No crees un `config/` nuevo dentro de un módulo. Usa `Config::get()` con los archivos existentes.
- No uses `$_GET`, `$_POST`, `$_SERVER` directamente. Usa `$request->`.
- No hagas `echo` ni `header()` directamente. Usa `Response::`.
- No inlines SQL con valores de usuario. Siempre usa PDO con `?` placeholders.
- No instancies `new PDO()` dentro de clases. El container inyecta el singleton.
- No uses APCu directamente. Usa `CacheInterface`.
- No hagas `json_encode()` para respuestas HTTP. Usa `Response::json()` o `Response::success()`.
- No agregues lógica de negocio en controllers. Solo coordinan request → service → response.
- No agregues lógica de base de datos en services. Solo coordinan service → repository.
- No crees helpers globales ni funciones sueltas. Usa clases.
