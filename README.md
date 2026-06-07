# Modux

A production-ready PHP modular monolith framework. Each business domain lives in its own self-contained module. No facades, no magic statics, no hidden globals — every dependency is explicit and injected.

**Best for:** teams that want full control over their codebase, clear request lifecycles, and testable code without learning a large framework's conventions.

---

## At a glance

```
Request → Kernel → Global pipeline (CORS, RequestSize, SecurityHeaders, Logger)
                 → Route middlewares (Auth?, Admin?, Tenant?)
                 → Controller (typed injection via reflection)
                 → Response (always JSON, never echo+exit)
```

- **Zero magic** — no facades, no service locator calls in business code
- **PSR-11 container** with reflection-based autowiring and `makeWith` for parameterized resolution
- **PSR-3 structured logger** — JSON to file or stderr, falls back silently
- **Middleware pipeline** — composable per-route and per-group, immutable via clone
- **FormRequest** — validates on construction, throws `ValidationException` (422) automatically
- **Exception hierarchy** — typed exceptions map directly to HTTP status codes
- **JWT + refresh token rotation** — opaque refresh tokens, per-user revocation
- **Rate limiting** — `CacheInterface`-backed (APCu in production, Array in tests), graceful no-op
- **RBAC** — `PermissionMiddleware` checks `roles_permisos` at runtime via parameterized middleware
- **Event system** — synchronous `EventDispatcher` with `listen()` / `dispatch()`
- **Multi-tenancy** — row-level isolation via `TenantMiddleware` + JWT `tenant_id` claim (optional)
- **Versioned migrations** — tracked with batch numbers, supports `rollback` and `fresh`
- **152 unit tests**, PHPStan level 6 clean, PHPCS PSR-12

---

## Requirements

- PHP 8.2+
- MySQL 8.0+ (or any PDO-compatible database)
- Composer

---

## Installation

```bash
git clone <repo> my-project
cd my-project
composer install
cp .env.example .env
# Edit .env — see Environment Variables section
```

---

## Quick start

```bash
# 1. Configure environment
cp .env.example .env
# Set JWT_SECRET, DB_HOST, DB_NAME, DB_USER, DB_PASS

# 2. Run migrations
cd backend/src
php modux migrate

# 3. Start the server
php -S localhost:8080 -t public/
```

```bash
# Login
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"admin@admin.com","clave":"admin123"}'
```

```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
    "refresh_token": "a8f3c1d9e..."
  }
}
```

```bash
# Health check
curl http://localhost:8080/health
```

```json
{
  "success": true,
  "data": { "status": "ok", "php": "8.2.0", "db": "ok" }
}
```

---

## Project structure

```

├── app/
│   ├── Exceptions/         # Exception hierarchy + global JSON handler
│   ├── Helpers/            # PaginatorHelper, EmailHelper
│   ├── Http/
│   │   ├── Controllers/    # Infrastructure controllers (HealthController, LogsController)
│   │   └── Middleware/     # CorsMiddleware, AuthMiddleware, AdminMiddleware,
│   │                       # TenantMiddleware, PermissionMiddleware,
│   │                       # SecurityHeadersMiddleware, RequestSizeLimitMiddleware,
│   │                       # RequestLoggerMiddleware
│   ├── Modules/            # Business domain modules
│   │   └── {Name}/
│   │       ├── Controllers/
│   │       ├── Repositories/
│   │       ├── Requests/         # Extend FormRequest
│   │       ├── Services/
│   │       ├── ServiceProvider.php  # Optional — auto-discovered at boot
│   │       └── routes.php
│   └── Support/            # Framework core
│       ├── Cache/              # ApcuCache, ArrayCache (implement CacheInterface)
│       ├── Config.php          # Static config loader (config/*.php files)
│       ├── Container.php       # PSR-11 DI container with autowiring + makeWith
│       ├── DB.php              # withTransaction() helper
│       ├── EventDispatcher.php # Synchronous event bus
│       ├── FormRequest.php     # Validated request base class
│       ├── JWTConfig.php       # JWT encode/decode/refresh helpers
│       ├── Kernel.php          # HTTP kernel — creates Request, dispatches
│       ├── Logger.php          # PSR-3 structured JSON logger
│       ├── LogReader.php       # Reads and parses app.log
│       ├── Pipeline.php        # Immutable middleware pipeline
│       ├── RateLimiter.php     # CacheInterface-backed rate limiting
│       ├── Request.php         # HTTP request wrapper
│       ├── Response.php        # Immutable JSON response (with getHeaders())
│       ├── Roles.php           # Role constants (ADMIN, USER)
│       ├── Router.php          # Route registration + dispatch + prefix groups
│       ├── ServiceProvider.php # Base provider (register/boot lifecycle)
│       ├── UUIDGenerator.php   # UUID v4 generation
│       ├── Validator.php       # Validation engine
│       └── Contracts/          # CacheInterface, MiddlewareInterface, ServiceProviderInterface
├── modux                   # CLI entry point
├── bootstrap/
│   ├── app.php             # Boot sequence (9 stages)
│   └── test.php            # Test bootstrap (skips HTTP dispatch)
├── config/
│   ├── app.php             # App settings, trusted proxies, request size
│   ├── auth.php            # JWT secret, TTL, algorithm
│   ├── cors.php            # Allowed origins, methods, headers
│   ├── database.php        # PDO connection config
│   ├── logging.php         # Channel, driver, level, path
│   └── mail.php            # SMTP settings
├── migrations/             # 0001_*.php, 0002_*.php, ...
├── public/index.php        # 3-line entry point
├── seeders/
└── tests/
    ├── Feature/            # Full HTTP dispatch, real DB, transaction rollback
    └── Unit/               # Mocked repositories, no DB
```

---

## CLI — `php modux`

```
php modux make:module <Name> [--with-tenant]   Scaffold a complete module
php modux make:migration <name>                Create a versioned migration file
php modux make:test <Name>                     Generate a unit test stub
php modux migrate                              Run all pending migrations
php modux migrate:rollback                     Roll back the last migration batch
php modux migrate:fresh                        Rollback all + re-run all migrations
php modux routes                               List every registered route
```

### `make:module`

```bash
php modux make:module Producto
php modux make:module Factura --with-tenant   # tenant-scoped repository + TenantMiddleware
```

Generates `app/Modules/Producto/` with:

```
Controllers/ProductoController.php
Repositories/ProductoRepository.php   (or tenant-scoped variant)
Services/ProductoService.php
Requests/CreateProductoRequest.php
Requests/UpdateProductoRequest.php
routes.php                            (or with TenantMiddleware)
```

The module is **auto-discovered** — `bootstrap/app.php` globs `app/Modules/*/routes.php` at boot. No manual registration needed. Optionally add `app/Modules/{Name}/ServiceProvider.php` — it will also be auto-discovered.

### `make:migration`

```bash
php modux make:migration create_productos_table
# → migrations/0002_create_productos_table.php
```

Files are numbered sequentially. Each file returns an anonymous class with `up(PDO)` and `down(PDO)`.

### `migrate`

```bash
php modux migrate
```

```
  migrated   0001_create_base_tables.php
  skipped    0002_create_clientes_table.php   ← already ran

  1 migration(s) ran.
```

Tracks ran migrations in a `migrations` table with a `batch` number. Safe to run on every deploy.

### `migrate:rollback` and `migrate:fresh`

```bash
php modux migrate:rollback   # undo last batch
php modux migrate:fresh      # rollback all + re-run all (resets to clean state)
```

### `routes`

```bash
php modux routes
```

```
  METHOD    URI                         HANDLER                        MIDDLEWARES
  ─────────────────────────────────────────────────────────────────────────────────
  POST      /auth/login                 AuthController@login
  POST      /auth/refresh               AuthController@refresh
  POST      /auth/logout                AuthController@logout           AuthMiddleware
  POST      /auth/impersonate           AuthController@impersonate      Auth, Admin, Tenant
  GET       /clientes                   ClienteController@index         Auth, Tenant
  GET       /health                     HealthController@check
```

Does not require a database connection.

---

## Boot sequence

`bootstrap/app.php` boots in 9 ordered stages:

| Stage | What happens |
|---|---|
| 1 | Load `.env`, enforce required vars |
| 2 | Set config path |
| 3 | Set error reporting, disable display_errors |
| 4 | Build PSR-11 Container |
| 5 | Register Logger singleton + global exception handler |
| 6 | Register PDO, DB, and CacheInterface (ApcuCache) singletons |
| 7 | Register Router + Kernel singletons |
| 7.5 | Register EventDispatcher; auto-discover + boot module ServiceProviders |
| 8 | Auto-discover module `routes.php` files |
| 9 | Register infrastructure routes (health, logs) |

Stage 7.5 calls `register()` then `boot()` on every `app/Modules/*/ServiceProvider.php` that exists. This is where modules subscribe to events and override container bindings.

---

## Creating a module

### Repository

```php
namespace App\Modules\Producto\Repositories;

use PDO;
use App\Exceptions\NotFoundException;

class ProductoRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        return $this->pdo->query('SELECT * FROM productos')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed> */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Producto', $id);
        }

        return $row;
    }

    /** @return array<string, mixed> */
    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare('INSERT INTO productos (nombre, precio) VALUES (?, ?)');
        $stmt->execute([$data['nombre'], $data['precio']]);
        return $this->findById((int) $this->pdo->lastInsertId());
    }
}
```

### Service

```php
namespace App\Modules\Producto\Services;

use App\Modules\Producto\Repositories\ProductoRepository;

class ProductoService
{
    public function __construct(private ProductoRepository $repository) {}

    public function getAll(): array       { return $this->repository->findAll(); }
    public function get(int $id): array   { return $this->repository->findById($id); }
    public function create(array $d): array { return $this->repository->create($d); }
}
```

### Controller

```php
namespace App\Modules\Producto\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Modules\Producto\Services\ProductoService;
use App\Modules\Producto\Requests\CreateProductoRequest;

class ProductoController
{
    public function __construct(private ProductoService $service) {}

    public function index(Request $request): Response
    {
        return Response::success($this->service->getAll());
    }

    public function show(Request $request): Response
    {
        return Response::success($this->service->get((int) $request->route('id')));
    }

    public function create(CreateProductoRequest $request): Response
    {
        return Response::success($this->service->create($request->validated()), 201);
    }
}
```

### ServiceProvider

```php
namespace App\Modules\Producto;

use App\Support\ServiceProvider;
use App\Modules\Producto\Repositories\ProductoRepository;
use App\Modules\Producto\Services\ProductoService;
use App\Modules\Producto\Controllers\ProductoController;

class ProductoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(ProductoRepository::class, fn ($c) =>
            new ProductoRepository($c->get(\PDO::class))
        );
        $this->container->singleton(ProductoService::class, fn ($c) =>
            new ProductoService($c->get(ProductoRepository::class))
        );
        $this->container->singleton(ProductoController::class, fn ($c) =>
            new ProductoController($c->get(ProductoService::class))
        );
    }

    public function boot(): void
    {
        $router = $this->container->get(\App\Support\Router::class);
        require __DIR__ . '/routes.php';
    }
}
```

---

## Routing

### Individual routes

```php
// Public
$router->post('/auth/login', [AuthController::class, 'login']);

// With middlewares
$router->get('/productos/{id}', [ProductoController::class, 'show'],
    [AuthMiddleware::class]);

$router->delete('/productos/{id}', [ProductoController::class, 'delete'],
    [AuthMiddleware::class, AdminMiddleware::class]);
```

### Route groups — share middlewares and URI prefix

```php
// Middleware group
$router->group([AuthMiddleware::class], function ($router) {
    $router->get('/productos',      [ProductoController::class, 'index']);
    $router->post('/productos',     [ProductoController::class, 'create']);
    $router->put('/productos/{id}', [ProductoController::class, 'update']);
});

// Prefix group — all routes get /v1 prepended
$router->group([AuthMiddleware::class], '/v1', function ($router) {
    $router->get('/productos', [ProductoController::class, 'index']); // → GET /v1/productos
});

// Nested groups — middlewares and prefixes are merged, not replaced
$router->group([AuthMiddleware::class], function ($router) {
    $router->group([AdminMiddleware::class], function ($router) {
        $router->get('/admin/roles', [AdminController::class, 'roles']);
    });
});

// Parameterized middleware — RBAC permission check
$router->delete('/productos/{id}', [ProductoController::class, 'delete'], [
    AuthMiddleware::class,
    PermissionMiddleware::class . ':productos.delete',
]);
```

Route parameters are extracted automatically and available via `$request->route('id')`.

### Controller injection

The router resolves controller method parameters by type:

| Parameter type | What gets injected |
|---|---|
| `Request` | The current request (with `user()`, `tenantId()`, route params already set) |
| Subclass of `FormRequest` | A new instance constructed from `$request->all() + routeParams()` — validated on construction |
| Any other class | Resolved from the container |
| Scalar (untyped) | `$request->route($paramName)` |

**Dual-parameter pattern** — use when you need both the request context (tenantId, user) and a validated FormRequest:

```php
public function create(Request $request, CreateProductoRequest $validated): Response
{
    $tenantId = (string) $request->tenantId();
    return Response::success($this->service->create($validated->validated(), $tenantId), 201);
}
```

---

## Request API

```php
// Input — priority: route params > JSON body > POST > GET
$request->input('key');
$request->input('key', 'default');
$request->all();                   // all merged inputs
$request->only(['campo1', 'campo2']);
$request->except(['_token']);

// Route parameters (from URI segments like {id})
$request->route('id');

// HTTP metadata
$request->method();                // 'GET', 'POST', etc.
$request->uri();                   // '/path/only' (no query string)
$request->header('X-Custom');
$request->bearerToken();           // extracts from Authorization: Bearer <token>
$request->ip();                    // client IP, respects trusted proxies

// Middleware-set context
$request->user();                  // array payload from JWT (set by AuthMiddleware)
$request->tenantId();              // string (set by TenantMiddleware)

// Type checks
$request->isJson();                // true if Content-Type: application/json

// Magic property access
$request->nombre;                  // same as $request->input('nombre')
```

---

## Response API

Response is immutable — every method returns a new instance.

```php
// Success responses
Response::success($data);           // 200
Response::success($data, 201);      // 201 Created

// Error responses
Response::error('Not allowed.', 403);

// Redirect
Response::redirect('/new-path', 302);

// Builder pattern (immutable — each method returns a new instance)
(new Response())
    ->withStatus(200)
    ->withHeader('X-Custom', 'value')
    ->json(['key' => 'value']);

// Inspect without sending
$response->getStatus();            // int
$response->getHeaders();           // array<string, string>

// Send (called once by Kernel)
$response->send();
```

Success response shape:

```json
{ "success": true, "data": { ... } }
```

Error response shape (from typed exceptions):

```json
{ "success": false, "message": "Not found." }
```

Validation error shape:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["email is required.", "email must be a valid email address."],
    "precio": ["precio must be an integer."]
  }
}
```

---

## Request validation

Extend `FormRequest` — validation runs on construction and throws `ValidationException` (HTTP 422) automatically.

```php
namespace App\Modules\Producto\Requests;

use App\Support\FormRequest;

class CreateProductoRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'nombre'    => 'required|min:2|max:100',
            'precio'    => 'required|integer',
            'activo'    => 'boolean',
            'tipo'      => 'required|in:fisico,digital',
            'url_foto'  => 'nullable|url',
            'sku'       => 'nullable|regex:/^[A-Z]{2}-\d{4}$/',
            'lanzado'   => 'nullable|date',
            'ext_id'    => 'nullable|uuid',
        ];
    }
}
```

### `all()` vs `validated()`

```php
// Request body: {"nombre":"Mesa","precio":150,"admin":true}
// Rules: {nombre, precio}

$request->all()        // {"nombre":"Mesa","precio":150,"admin":true}
$request->validated()  // {"nombre":"Mesa","precio":150}  ← only declared fields
```

Always use `validated()` in business logic — it prevents mass-assignment by design.

### Validation rules

| Rule | Example | Description |
|---|---|---|
| `required` | `required` | Present and non-empty |
| `email` | `email` | Valid email format |
| `min:N` | `min:6` | Minimum string length (multibyte-aware) |
| `max:N` | `max:255` | Maximum string length (multibyte-aware) |
| `integer` | `integer` | Must be an integer value |
| `numeric` | `numeric` | Must be numeric (int or float) |
| `boolean` | `boolean` | `true`, `false`, `0`, `1`, `'0'`, `'1'` |
| `string` | `string` | Must be a PHP string type |
| `array` | `array` | Must be a PHP array type |
| `in:a,b,c` | `in:admin,user` | Must be one of the listed values |
| `url` | `url` | Valid URL (`filter_var FILTER_VALIDATE_URL`) |
| `date` | `date` | Valid date in `Y-m-d` format (default) |
| `date:format` | `date:d/m/Y` | Valid date in custom format |
| `regex:/pattern/` | `regex:/^\d{4}$/` | Matches the given regular expression |
| `uuid` | `uuid` | Valid UUID v4 format |
| `confirmed` | `confirmed` | Matches `{field}_confirmation` sibling |
| `nullable` | `nullable` | Skip all rules if field is absent or empty string |

Rules are composable with `|`:

```php
'email' => 'required|email|max:255',
'rol'   => 'nullable|in:1,2,3',
```

---

## Exceptions → HTTP responses

Throw a typed exception anywhere — the global handler converts it to JSON automatically.

```php
throw new AuthException('Invalid credentials.');       // 401
throw new ForbiddenException('Admin only.');           // 403
throw new NotFoundException('Producto', $id);          // 404
throw new ValidationException(['campo' => ['msg']]);   // 422
throw new RateLimitException('Too many attempts.');    // 429
throw new DatabaseException('Query failed.');          // 500 (message hidden in prod)
```

| Exception | HTTP | Notes |
|---|---|---|
| `AuthException` | 401 | Invalid/missing/revoked token |
| `ForbiddenException` | 403 | Authenticated but not authorized |
| `NotFoundException` | 404 | Resource or route not found |
| `MethodNotAllowedException` | 405 | Right path, wrong HTTP method |
| `ValidationException` | 422 | Carries a field → messages array |
| `RateLimitException` | 429 | Too many login attempts |
| `DatabaseException` | 500 | DB errors; message hidden when `APP_DEBUG=false` |

All exceptions extend `AppException`. Unhandled `Throwable` returns 500 with the exception detail hidden in production.

---

## Middleware

| Middleware | Applied | Effect |
|---|---|---|
| `CorsMiddleware` | All requests | CORS headers; handles OPTIONS preflight |
| `RequestSizeLimitMiddleware` | All requests | Rejects bodies over `app.max_request_size` (default 2 MB) |
| `SecurityHeadersMiddleware` | All requests | X-Frame-Options, X-Content-Type-Options, Referrer-Policy, etc. |
| `RequestLoggerMiddleware` | All requests | Structured JSON log entry: method, URI, status, duration |
| `AuthMiddleware` | Protected routes | Decodes JWT, validates token is not revoked, sets `$request->user()` |
| `AdminMiddleware` | Admin routes | Requires `user['rol'] === 1`, throws 403 otherwise |
| `TenantMiddleware` | Tenant-scoped routes | Reads `tenant_id` from JWT payload, sets `$request->tenantId()` |
| `PermissionMiddleware` | RBAC routes | Checks `roles_permisos` table for the given permission key (403 if not granted) |

The global pipeline (`CorsMiddleware → RequestSizeLimitMiddleware → SecurityHeadersMiddleware → RequestLoggerMiddleware`) runs on every request before any route middleware.

### Writing a middleware

```php
namespace App\Http\Middleware;

use App\Support\Request;
use App\Support\Response;
use App\Support\Contracts\MiddlewareInterface;

class AuditMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        // post-processing here
        return $response;
    }
}
```

---

## Authentication

### Login

```
POST /auth/login
Content-Type: application/json

{"usuario": "email@example.com", "clave": "password"}
```

Returns `access_token` (JWT) and `refresh_token` (opaque, stored in DB).

The JWT payload contains `sub` (user ID), `tenant_id`, and expiry. Default TTL: 86400 seconds (configurable via `JWT_TTL`).

### Token refresh

```
POST /auth/refresh
Content-Type: application/json

{"refresh_token": "a8f3c1d9..."}
```

Issues a new `access_token` + `refresh_token` pair. The old refresh token is deleted immediately (rotation — each token is single-use).

### Logout

```
POST /auth/logout
Authorization: Bearer <access_token>
Content-Type: application/json

{"refresh_token": "a8f3c1d9..."}   ← optional, also invalidates refresh token
```

### Impersonation (admin only)

```
POST /auth/impersonate
Authorization: Bearer <admin_access_token>
Content-Type: application/json

{"target_id": 42}
```

- Requires `AuthMiddleware + AdminMiddleware + TenantMiddleware`
- Admin can only impersonate users within their own tenant
- Returns a JWT signed as the target user

### Rate limiting

Login attempts are tracked per username using APCu. After 5 failed attempts the account is locked for 5 minutes (`RateLimitException` → 429). If APCu is not installed, rate limiting is silently skipped.

### Authenticated request

```
GET /any-protected-route
Authorization: Bearer <access_token>
```

`AuthMiddleware` resolves the request through **guards** (JWT first, then API
key) and produces a unified `Principal`. For backward compatibility it still
calls `$request->setUser($payload)`, so `$request->user()`,
`TenantMiddleware` and `PermissionMiddleware` work unchanged. Use
`$request->principal()` to read the auth type, tenant and scopes.

### API keys (third-party auth)

For server-to-server access by external developers, the same protected routes
accept an **API key** instead of a user JWT — no code change on the route:

```
GET /any-protected-route
Authorization: Bearer mk_live_<id>_<secret>     # or: X-Api-Key: mk_live_...
```

Keys are issued with `App\Support\Auth\ApiKeyManager::issue($tenantId, $name, $scopes)`,
which returns the token **once** (only `prefix` + a SHA-256 `hash` are stored).

Tenants manage their own keys through the built-in `ApiKeys` module (CRUD):

```
POST   /api-keys          { "name": "...", "scopes": ["clientes.read"] }  → 201, token shown once
GET    /api-keys                                                          → list (never exposes hash)
GET    /api-keys/{id}                                                     → one key's metadata
DELETE /api-keys/{id}                                                     → revoke
```

These routes require `AuthMiddleware + TenantMiddleware + ScopeMiddleware:apikeys.manage`,
so app users (scope `*`) manage keys transparently, while an API key can only
administer others if explicitly granted `apikeys.manage` (prevents privilege
escalation). Every operation is scoped to the caller's tenant.

The key carries its tenant and a list of **scopes**; guard against them per route
with the parametrized middleware:

```php
$router->get('/clientes', [ClienteController::class, 'index'],
    [AuthMiddleware::class, TenantMiddleware::class, 'App\Http\Middleware\ScopeMiddleware:clientes.read']);
```

Scopes (what a credential may touch) are orthogonal to RBAC permissions (what a
user role may do) and to tenant entitlements (what a tenant has) — see
`docs/adr/0001-saas-identity-entitlements-billing.md`.

### Webhook signatures

`App\Support\Webhook\WebhookVerifier` (bound to `WebhookVerifierInterface`) hardens
inbound/outbound webhooks with a dependency-free scheme:

```
X-Signature: t=<unix_ts>,v1=<hex_hmac_sha256>
signature  = HMAC-SHA256("<ts>.<rawBody>", secret)
```

`verify($request, $secret, $tolerance = 300)` returns true only if the HMAC matches
(constant-time), the timestamp is within the window, **and** the signature hasn't been
seen before (anti-replay via `CacheInterface`, TTL = window). `sign($payload, $secret)`
produces the header for outbound webhooks. Inject the interface in any controller that
receives provider callbacks (e.g. payment gateways) and verify against that integration's
secret before acting. Reading the raw body relies on `Request::rawBody()`.

---

## DI Container

PSR-11 compliant with reflection-based autowiring.

```php
// Register a factory
$app->bind(MyService::class, fn ($c) => new MyService($c->get(PDO::class)));

// Register a singleton (resolved once, reused)
$app->singleton(MyService::class, fn ($c) => new MyService($c->get(PDO::class)));

// Register a pre-built instance
$app->instance(\PDO::class, $existingPdo);

// Resolve
$service = $app->get(MyService::class);

// Autowire without registration (uses reflection)
$service = $app->make(MyService::class);

// Autowire and inject extra scalar params into builtin constructor parameters
$middleware = $app->makeWith(PermissionMiddleware::class, 'facturas.delete');
```

Autowiring resolves constructor parameters by type name. If no binding exists for a type, it recursively resolves the class. Scalar parameters without defaults throw `ContainerException`. `makeWith` injects additional scalars positionally into builtin-typed parameters — used internally by the Router for parameterized middlewares.

---

## Multi-tenancy

The framework ships with row-level multi-tenancy. It is **opt-in** — if you don't include `TenantMiddleware` on a route, tenantId is never set and no tenant scoping happens.

### How it works

1. The `usuarios` table has a `tenant_id CHAR(36)` column (FK → `tenants.id`)
2. On login, `tenant_id` is embedded in the JWT payload
3. `TenantMiddleware` reads `tenant_id` from the decoded JWT and calls `$request->setTenantId()`
4. Controllers read `$request->tenantId()` and pass it down to repositories
5. Repositories add `AND tenant_id = ?` to their queries when `$tenantId !== null`

```php
// Route — add TenantMiddleware to enable scoping
$router->group([AuthMiddleware::class, TenantMiddleware::class], function ($router) {
    $router->get('/productos', [ProductoController::class, 'index']);
});
```

```php
// Controller
public function index(Request $request): Response
{
    return Response::success($this->service->getAllForTenant($request->tenantId()));
}
```

```php
// Repository — conditional scoping
public function findAll(?string $tenantId = null): array
{
    $sql    = 'SELECT * FROM productos';
    $params = [];

    if ($tenantId !== null) {
        $sql    .= ' WHERE tenant_id = ?';
        $params[] = $tenantId;
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### Running without multi-tenancy

Simply don't add `TenantMiddleware` to any route. The `tenant_id` column in `usuarios` can be omitted. Repositories receive `null` and skip the tenant filter. No other changes needed.

### Admin impersonation across tenants

An admin can only impersonate users within their own tenant. Attempting cross-tenant impersonation throws `AuthException(403)`. Passing `$adminTenantId = null` skips this check (internal use only — the route always passes the real tenant ID via `TenantMiddleware`).

---

## Config

```php
Config::get('auth.jwt_secret');         // config/auth.php → jwt_secret
Config::get('app.debug', false);        // with default
Config::get('cors.allowed_origins');    // array

Config::all('database');                // entire config/database.php as array
```

Config files live in `config/` and are plain PHP files returning arrays. Values map to env vars via `$_ENV`.

---

## Logger

PSR-3 compliant. Inject via constructor:

```php
public function __construct(
    private ProductoRepository $repository,
    private \App\Support\Logger $logger,
) {}

public function delete(int $id): void
{
    $this->logger->info('Deleting product', ['id' => $id]);
    $this->repository->delete($id);
    $this->logger->error('DB error', ['exception' => $e->getMessage()]);
}
```

Output to `storage/logs/app.log` (structured JSON, one entry per line):

```json
{"timestamp":"2026-04-25T15:30:00+00:00","level":"info","message":"Deleting product","context":{"id":42}}
```

Log levels (in order): `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`

The minimum level is controlled by `LOG_LEVEL`. Messages below it are silently dropped.

If the log file cannot be written, the logger falls back to `STDERR` automatically — no silent failures.

---

## Pagination

`PaginatorHelper` wraps any SQL query and reads `page` / `perPage` from query parameters automatically.

```php
public function list(?string $tenantId = null): array
{
    $sql    = 'SELECT * FROM productos WHERE activo = 1';
    $params = [];

    if ($tenantId !== null) {
        $sql    .= ' AND tenant_id = ?';
        $params[] = $tenantId;
    }

    return (new PaginatorHelper($this->pdo, $sql, $params))->getPaginatedResults();
}
```

Query parameters accepted:

| Param | Default | Description |
|---|---|---|
| `page` | `1` | Current page (1-indexed) |
| `perPage` | `10` | Items per page |
| `paginate` | `true` | Set to `false` to return all results unpaged |

Response shape (always HTTP 200, even when `results` is empty):

```json
{
  "total": 42,
  "cantidad_por_pagina": 10,
  "pagina": 2,
  "cantidad_total": 42,
  "results": [...]
}
```

LIMIT and OFFSET are bound via PDO prepared statements. `perPage` and `page` are cast to integers.

---

## Migrations

```php
// migrations/0002_create_productos_table.php
return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS productos (
                id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
                nombre     VARCHAR(255) NOT NULL,
                precio     INT          NOT NULL DEFAULT 0,
                tenant_id  CHAR(36)     NOT NULL,
                created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                CONSTRAINT fk_productos_tenant
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS productos');
    }
};
```

---

## Testing

```bash
composer test      # PHPUnit (220 tests)
composer lint      # phpcs PSR-12
composer analyse   # phpstan level 6 (PHPStan 2.x)
```

### Quality gate — don't push a broken base

This is a versioned framework that others depend on, so the same checks run in three places:

- **Local pre-push hook** (`.githooks/pre-push`) — blocks `git push` unless `composer validate`,
  lint, static analysis and tests all pass. Enable it once per clone:

  ```bash
  git config core.hooksPath .githooks
  ```

  Bypass only in an emergency with `git push --no-verify`.

- **CI** (`.github/workflows/ci.yml`) — runs the same gate plus a Docker image build on every
  push and PR to `main`.

### Unit tests — mock repositories, no DB

```php
class ProductoServiceTest extends UnitTestCase
{
    private ProductoRepository $repository;
    private ProductoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ProductoRepository::class);
        $this->service    = new ProductoService($this->repository);
    }

    public function test_throws_not_found_when_product_missing(): void
    {
        $this->repository
            ->method('findById')
            ->willThrowException(new NotFoundException('Producto', 99));

        $this->expectException(NotFoundException::class);
        $this->service->get(99);
    }
}
```

`UnitTestCase` provides:
- `setUp()` — clears superglobals before each test
- `makeRequest(?array $user, ?string $tenantId): Request` — builds a Request with pre-set user/tenant context

### Feature tests — full HTTP dispatch, real DB, auto-rollback

```php
class ProductoFeatureTest extends FeatureTestCase
{
    public function test_create_returns_201(): void
    {
        $token = $this->loginAs('admin@admin.com', 'admin123');

        $response = $this->post('/productos', [
            'nombre' => 'Mesa',
            'precio' => 150,
        ], $token);

        $this->assertTrue($response['success']);
        $this->assertSame(201, $this->lastStatus());
    }
}
```

Each feature test wraps its DB operations in a transaction that rolls back in `tearDown()`.

---

## Events

`EventDispatcher` provides a synchronous in-process event bus. Inject it anywhere via the container.

```php
// Subscribe in ServiceProvider::boot()
$dispatcher->listen('usuario.created', function (array $payload): void {
    // send welcome email, log audit trail, etc.
    // $payload = ['id' => 42, 'email' => 'user@example.com']
});

// Dispatch from a Service
$this->dispatcher->dispatch('usuario.created', [
    'id'    => $id,
    'email' => $data['email'],
]);

// Check if anyone is listening
$dispatcher->hasListeners('usuario.created'); // bool
```

Events are synchronous — the caller waits for all listeners to finish. For fire-and-forget behaviour wrap the listener body in a `try/catch`.

---

## RBAC — permission-based access control

Assign permission keys to roles via the `roles_permisos` table (each row links a `rol_id` to a `permiso_id`). Use `PermissionMiddleware` on routes that require a specific permission:

```php
use App\Http\Middleware\PermissionMiddleware;

$router->group([AuthMiddleware::class, TenantMiddleware::class], function ($router) {
    $router->get('/facturas',       [FacturaController::class, 'index']);
    $router->post('/facturas',      [FacturaController::class, 'create'],   [PermissionMiddleware::class . ':facturas.write']);
    $router->delete('/facturas/{id}', [FacturaController::class, 'delete'], [PermissionMiddleware::class . ':facturas.delete']);
});
```

The middleware throws `ForbiddenException` (403) if the authenticated user's role does not have the requested permission. `AdminMiddleware` still covers simple admin-only gates; use `PermissionMiddleware` for fine-grained per-operation control.

---

## Entitlements — tenant feature gating

Entitlements answer "**what does this tenant have?**" — which modules/features, how many
seats, what quotas — independently of who the user is (RBAC) or what a credential may touch
(scopes). They live in `tenant_entitlements` and are read through
`EntitlementResolverInterface` (`App\Support\Entitlements\DbEntitlementResolver`).

Three types: `flag` (has / hasn't), `quota` (numeric limit per cycle), `seat` (seats).
`limit_value` null = unlimited. Features are namespaced (`ia.rag`, `bots.outbound`).

Gate a route with the parametrized middleware (after `TenantMiddleware`):

```php
$router->post('/ia/ask', [IAController::class, 'ask'],
    [AuthMiddleware::class, TenantMiddleware::class,
     EntitlementMiddleware::class . ':ia.rag']);
```

Missing/disabled feature → **402 Payment Required** (an actionable "upgrade your plan"
signal, distinct from 403). In code:

```php
$set = $resolver->for($tenantId);
$set->allows('ia.rag');             // bool (flag / gating)
$set->limit('api.calls');           // ?int (null = unlimited)
$set->remaining('api.calls', $used);// ?int, used passed in (no I/O in the value object)
```

**The base only reads `tenant_entitlements`.** It's populated by the optional billing
module (`source = 'billing:*'`) or by hand (`source = 'manual'`) — so product modules
(e.g. `modux-ia`) never depend on billing.

### Usage metering & quotas

Record usage via `UsageRecorderInterface` (`App\Support\Usage\DbUsageRecorder`, table
`usage_events`). Recording is **explicit** — the consuming code decides the cost per call:

```php
$usage->record($tenantId, 'api.calls', 1, $idempotencyKey);  // idempotency_key dedupes retries
$usage->record($tenantId, 'ia.tokens', $tokensUsed);
```

`QuotaMiddleware:<feature>` enforces the limit (after `TenantMiddleware`). It counts
`usage_events` from the entitlement's `period_start` (or the calendar month start when there's
no billing) and compares against the limit:

```php
$router->post('/ia/ask', [IAController::class, 'ask'],
    [AuthMiddleware::class, TenantMiddleware::class, QuotaMiddleware::class . ':api.calls']);
```

- no entitlement / disabled → **402**, unlimited (`limit_value` null) → passes,
- quota exhausted → **429** with `Retry-After` (seconds until the cycle resets).

Quota cycles are anchored to the subscription's `period_start/period_end` (denormalized into
`tenant_entitlements` by billing). Moving the window resets the quota **without deleting**
`usage_events` (kept for audit/rating). As a safety net for missed renewals:

```bash
php modux entitlements:roll-periods   # advances expired quota cycles by their own span; idempotent
```

See `docs/adr/0001-saas-identity-entitlements-billing.md` for the full design.

---

## Database transactions

`App\Support\DB` wraps operations in a PDO transaction with automatic rollback on any exception:

```php
class FacturaService
{
    public function __construct(
        private FacturaRepository $facturas,
        private LineaRepository   $lineas,
        private DB                $db,
    ) {}

    public function create(array $data): array
    {
        return $this->db->withTransaction(function () use ($data) {
            $factura = $this->facturas->create($data);
            foreach ($data['lineas'] as $linea) {
                $this->lineas->create($factura['id'], $linea);
            }
            return $factura;
        });
    }
}
```

Inject `DB` in any service; the container auto-wires it with the registered PDO singleton.

---

## Job queue

DB-backed async queue. Jobs are stored in a `jobs` table and processed by a worker process. Multiple workers can run in parallel — claiming is done with an atomic UUID `UPDATE`.

### Defining a job

```php
namespace App\Modules\Notificaciones\Jobs;

use App\Support\Container;
use App\Support\Job;

class SendWelcomeEmailJob extends Job
{
    public string $email = '';
    public string $name  = '';
    public string $queue = 'emails';   // override the default queue

    public function handle(Container $container): void
    {
        $container->get(MailService::class)->sendWelcome($this->email, $this->name);
    }
}
```

Public properties (except the framework-reserved `queue`, `maxAttempts`, `delaySeconds`) are serialized as JSON payload in the DB. Service dependencies are resolved from the Container when `handle()` runs.

### Dispatching

```php
// Inject JobDispatcher in any service constructor
public function __construct(private JobDispatcher $dispatcher) {}

$job        = new SendWelcomeEmailJob();
$job->email = $data['email'];
$job->name  = $data['nombre'];
$this->dispatcher->dispatch($job);

// Dispatch with a delay (seconds before the job becomes available)
$job->delaySeconds = 300;
$this->dispatcher->dispatch($job);
```

### Running the worker

```bash
php modux queue:work                           # process 'default' queue, sleep 3s between polls
php modux queue:work --queue=emails            # process a specific queue
php modux queue:work --queue=emails --sleep=5  # custom sleep interval
php modux queue:work --once                    # process one job then exit (useful for cron)
php modux queue:work --timeout=10              # release jobs stuck > 10 minutes
```

SIGINT / SIGTERM (Ctrl-C) triggers a graceful shutdown — the worker finishes the current job before stopping.

For production, manage the worker with **supervisord** or **systemd** so it restarts automatically if it crashes.

### Failed jobs
        
On failure the job is retried up to `maxAttempts` times (default 3) with exponential back-off: `2^attempts` seconds between retries. After the last attempt the job row is marked `status = 'failed'` with the full error message stored.

```bash
php modux queue:failed          # list all failed jobs
php modux queue:retry 42        # reset job #42 to 'pending' so the worker picks it up again
php modux queue:flush           # delete all failed jobs
```

### `jobs` table schema

| Column | Type | Description |
|---|---|---|
| `id` | INT AUTO_INCREMENT | Primary key |
| `queue` | VARCHAR(100) | Queue name |
| `payload` | MEDIUMTEXT | JSON-serialized class + data |
| `attempts` | INT | How many times the worker tried |
| `max_attempts` | INT | Copied from Job at dispatch time |
| `status` | ENUM | `pending`, `running`, `failed` |
| `available_at` | DATETIME | When the job becomes eligible (supports delay) |
| `reserved_at` | DATETIME | When a worker claimed it |
| `reserved_by` | CHAR(36) | UUID of the worker that claimed it (atomic lock) |
| `failed_at` | DATETIME | When the job was finally marked failed |
| `error` | TEXT | Exception message + trace |

---

## Health check

```
GET /health
```

Returns 200 when DB is reachable, 503 when degraded:

```json
{ "success": true, "data": { "status": "ok", "php": "8.2.0", "db": "ok" } }
```

```json
{ "success": true, "data": { "status": "degraded", "php": "8.2.0", "db": "unreachable" } }
```

Use this endpoint for load balancer health probes, uptime monitors, and deploy scripts.

---

## Environment variables

Copy `.env.example` → `.env`. Required at boot (missing variables throw immediately):

| Variable | Description |
|---|---|
| `JWT_SECRET` | Min 32 chars. Generate: `php -r "echo bin2hex(random_bytes(32));"` |
| `DB_HOST` | Database host |
| `DB_NAME` | Database name |
| `DB_USER` | Database user |
| `DB_PASS` | Database password |

Optional:

| Variable | Default | Description |
|---|---|---|
| `APP_ENV` | `local` | `local` / `production` |
| `APP_DEBUG` | `false` | Expose exception details in JSON responses |
| `JWT_TTL` | `86400` | Access token lifetime in seconds |
| `JWT_REFRESH_TTL` | `604800` | Refresh token lifetime in seconds (7 days) |
| `JWT_ALGO` | `HS256` | JWT signing algorithm |
| `DB_PORT` | `3306` | Database port |
| `LOG_CHANNEL` | `file` | `file` or `stderr` |
| `LOG_LEVEL` | `debug` | Minimum log level to write |
| `CORS_ALLOWED_ORIGINS` | _(none)_ | Comma-separated list of allowed origins |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM` | — | SMTP credentials for `EmailHelper` |

---

## Optional: AI module (LLM + RAG)

Modux ships **without** any AI by default. AI is an opt-in add-on built on the standalone
[`cynchro/modux-ia`](https://packagist.org/packages/cynchro/modux-ia) SDK (LLM + RAG, no Python/Node).

```bash
composer require cynchro/modux-ia
```

Then add an `app/Modules/IA/` module (auto-discovered like any other) that wires the SDK
(`PhpAI\Bootstrap`, `PhpAI\DriverFactory`, `PhpAI\RAG\RAGEngine`) into controllers and exposes the
endpoints you need, e.g. `POST /ia/chat`, `/ia/ask`, `/ia/ingest`, `/ia/retrieve`.

Configure the driver via environment variables:

| Variable | Description |
|---|---|
| `AI_DRIVER` | `local` / `cloud` / `cluster` |
| `AI_CLOUD_PROVIDER` | e.g. `groq`, `openai` |
| `AI_CLOUD_API_KEY` | Provider API key |
| `AI_CLOUD_MODEL` | Chat/completion model |
| `AI_CLOUD_EMBEDDING_MODEL` | Embedding model (RAG) |
| `RAG_SQLITE_PATH` | Vector store path, e.g. `storage/ai/vectors.db` |

If you don't need AI, skip this section entirely — nothing in the core depends on it.

## Optional: Billing module

Modux ships **without** billing by default. It's an opt-in add-on: install the SDK + a
gateway adapter, and the bundled `app/Modules/Billing` activates itself (guarded by
`class_exists`, so the core stays clean when billing isn't installed).

```bash
composer require cynchro/modux-billing cynchro/modux-billing-stripe
# or, for Argentina:  cynchro/modux-billing-mp
```

The module exposes:

```
POST /billing/checkout              { "plan": "pro" }   → gateway checkout URL (auth + tenant)
POST /billing/webhook/{gateway}     (public, verified by signature)
```

The webhook is verified with the adapter's signature scheme, normalized, and applied via
`BillingManager::handleEvent()`, which writes the tenant's entitlements (`source =
billing:<gateway>`) — picked up by the **Entitlements** gating above. Configure gateways in
`config/billing.php` (env: `BILLING_GATEWAY`, `STRIPE_*`, `MP_*`).

> Architecture: the base only **reads** `tenant_entitlements`; billing **writes** it — so
> product modules never depend on billing. See
> `docs/adr/0001-saas-identity-entitlements-billing.md`.

---

## Why not Laravel?

| | Modux | Laravel |
|---|---|---|
| Runtime dependencies | ~5 | 30+ |
| Request lifecycle | 5 files | 50+ files |
| DI | Explicit constructor injection | Facades + service locator |
| Magic | None | `Auth::user()`, `DB::table()`, `Cache::get()`, ... |
| ORM | Raw PDO (you control every query) | Eloquent |
| Queue / Events | DB-backed async queue + synchronous `EventDispatcher` | Full async queue + broadcasting |
| Validation rules | 16 essential | 50+ |
| Learning curve | Read the source, understand everything | Learn the framework conventions |
| Suited for | Controlled APIs, internal tools, multi-tenant SaaS | Full-featured web apps |

If you need Eloquent, queues, broadcasting, or an ecosystem of packages — use Laravel.  
If you want to understand exactly what happens on every line of every request — use this.

---

## License

MIT
