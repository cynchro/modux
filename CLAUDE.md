# Modux — Developer Reference

> Quick reference for AI assistants and developers. Full documentation is in `README.md`.

## Architecture Overview

Production-ready PHP modular monolith. Each domain lives in `app/Modules/{Name}/`. The framework provides routing, PSR-11 DI, middleware pipeline, validation, JWT auth, multi-tenancy, and structured logging.

```
backend/src/
├── app/
│   ├── Exceptions/         # Exception hierarchy + global JSON handler
│   ├── Helpers/            # PaginatorHelper, EmailHelper
│   ├── Http/
│   │   ├── Controllers/    # Infrastructure controllers (HealthController, LogsController)
│   │   └── Middleware/     # CorsMiddleware, RequestSizeLimitMiddleware,
│   │                       # SecurityHeadersMiddleware, RequestLoggerMiddleware,
│   │                       # AuthMiddleware, AdminMiddleware, TenantMiddleware
│   ├── Modules/            # Business domains ONLY — see boundary rule below
│   │   └── {Name}/
│   │       ├── Controllers/
│   │       ├── Repositories/
│   │       ├── Requests/       # Extend FormRequest
│   │       ├── Services/
│   │       └── routes.php
│   └── Support/            # Framework core + cross-cutting services
│       ├── Config.php, Container.php (PSR-11), FormRequest.php
│       ├── JWTConfig.php, Kernel.php, Logger.php (PSR-3), LogReader.php, Pipeline.php
│       ├── RateLimiter.php, Roles.php, UUIDGenerator.php
│       ├── Request.php, Response.php, Router.php
│       ├── ServiceProvider.php, Validator.php
│       └── Contracts/      # MiddlewareInterface, ServiceProviderInterface
├── bootstrap/
│   ├── app.php             # 9-stage boot (env → container → logger → DB → modules → infra routes)
│   └── test.php            # Test bootstrap (no HTTP dispatch)
├── config/                 # app.php, auth.php, cors.php, database.php, logging.php, mail.php
├── migrations/             # 0001_*.php, 0002_*.php (tracked in `migrations` table)
├── public/index.php        # 3-line entry point
└── tests/
    ├── Feature/            # Full HTTP, real DB, transaction rollback
    └── Unit/               # Mocked repos, no DB
```

## Boundary rule: Modules vs Infrastructure

**`app/Modules/` is strictly for business domains** (Cliente, Factura, Producto, Usuario…).

System infrastructure does NOT go in `app/Modules/`. It belongs in:

| Concern | Location | Route registration |
|---|---|---|
| Business domain | `app/Modules/{Name}/` | Auto-discovered (glob) |
| HTTP infrastructure | `app/Http/Controllers/` | Explicit in bootstrap Stage 9 |
| Cross-cutting services | `app/Support/` | Auto-wired by container |

Examples of infrastructure (never a module): health checks, log viewer, metrics, cache stats, queue status, feature flags.

## Request Lifecycle

```
index.php
  → bootstrap/app.php       (9-stage boot)
  → Kernel::handle()        (creates Request, dispatches)
  → Router::dispatch()      (matches URI, builds pipeline)
  → Global pipeline         (CORS → RequestSizeLimit → SecurityHeaders → RequestLogger)
  → Route middlewares       (Auth? → Admin? → Tenant?)
  → Controller::method()    (typed injection via reflection)
  → Response::send()        (JSON + headers)
```

## Key Conventions

### Adding a business module

```bash
php modux make:module <Name>
```

Generates controller, service, repository, requests, and `routes.php`. The module is **auto-discovered** — `bootstrap/app.php` globs `app/Modules/*/routes.php` at boot. No registration needed. The container auto-wires all dependencies via reflection.

### Adding an infrastructure route

1. Create the controller in `app/Http/Controllers/`.
2. Create the service (if needed) in `app/Support/`.
3. Register the route explicitly in `bootstrap/app.php` Stage 9.

### Route definition

```php
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/items', [ItemController::class, 'index'], [AuthMiddleware::class]);
$router->group([AuthMiddleware::class, TenantMiddleware::class], function ($router) {
    $router->get('/items', [ItemController::class, 'index']);
    $router->post('/items', [ItemController::class, 'create']);
});
```

### Controller injection patterns

```php
// Simple — FormRequest only
public function create(CreateItemRequest $request): Response
{
    return Response::success($this->service->create($request->validated()), 201);
}

// Dual-parameter — need tenantId AND validated data
public function create(Request $request, CreateItemRequest $validated): Response
{
    $tenantId = (string) $request->tenantId();
    return Response::success($this->service->create($validated->validated(), $tenantId), 201);
}
```

The Router injects `Request` (original, has tenantId/user set by middleware) and FormRequest subclasses (new instance, constructed from request data, validated on construction) as separate parameters.

### Exception handling

```php
throw new AuthException('Invalid token.');         // → 401
throw new ForbiddenException('Admin only.');       // → 403
throw new NotFoundException('Item', $id);          // → 404
throw new ValidationException(['field' => [...]]);// → 422
throw new RateLimitException('Too many tries.');   // → 429
throw new DatabaseException('Query failed');       // → 500
```

### Validation rules

`required`, `email`, `min:N`, `max:N`, `integer`, `numeric`, `boolean`, `string`, `array`, `in:a,b,c`, `url`, `date`, `date:format`, `regex:/pattern/`, `uuid`, `confirmed`, `nullable`

### Multi-tenancy

Opt-in. Add `TenantMiddleware` to routes. `TenantMiddleware` requires PDO injection (auto-wired by the container) and verifies the tenant exists in the DB before proceeding. Reads `tenant_id` from JWT payload, sets `$request->tenantId()`. Repositories accept `?string $tenantId` and scope `WHERE tenant_id = ?` when non-null. Without `TenantMiddleware`, tenantId is null and no scoping happens.

### Role constants

Use `App\Support\Roles` instead of magic numbers:

```php
use App\Support\Roles;

if ($user['rol'] === Roles::ADMIN) { ... }  // ADMIN = 1, USER = 0
```

### Rate limiting (APCu)

`App\Support\RateLimiter` provides APCu-backed rate limiting. Gracefully no-ops when APCu is unavailable (e.g. in tests).

```php
if ($this->rateLimiter->tooManyAttempts($key)) {
    throw new RateLimitException('Too many attempts.');
}
$this->rateLimiter->hit($key, ttlSeconds: 300);
$this->rateLimiter->clear($key);
```

### UUID generation

Use `App\Support\UUIDGenerator::v4()` anywhere a UUID v4 is needed. Do not inline the generation logic.

### Log reading

`App\Support\LogReader` reads and parses `storage/logs/app.log`. It is the **reader**. `App\Support\Logger` is the **writer**. Do not confuse the two.

### PaginatorHelper

`App\Helpers\PaginatorHelper` wraps a raw SQL query with `COUNT(*)` and `LIMIT/OFFSET`. The `$query` parameter **must be a static, hardcoded string**. Dynamic values must go through `$params` (PDO positional placeholders). Never pass user input as part of `$query`.

### Config access

```php
Config::get('auth.jwt_secret');
Config::get('app.debug', false);
Config::all('database');
```

### Logger (PSR-3)

```php
$this->logger->info('Message', ['context' => 'value']);
$this->logger->error('Failed', ['exception' => $e->getMessage()]);
```

JSON output to `storage/logs/app.log`. Falls back to STDERR if file is unwritable.

### CORS

Allowed origins are controlled by the `CORS_ALLOWED_ORIGINS` env var (comma-separated). The default is an empty list (deny all). **This must be set in production.** Wildcard `*` is supported but incompatible with `CORS_ALLOW_CREDENTIALS=true`.

## Testing

```bash
composer test      # PHPUnit (118 tests)
composer lint      # phpcs PSR-12
composer analyse   # phpstan level 6
```

`UnitTestCase` provides `makeRequest(?array $user, ?string $tenantId): Request`.  
`FeatureTestCase` provides `post()`, `get()`, `put()`, `delete()` helpers + DB transaction rollback.  
Inject JSON body in feature tests: `Request::setTestInputStream(json_encode($data))`.

## Environment Variables

Required at boot: `JWT_SECRET` (min 32 chars), `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.  
Optional but recommended in production: `CORS_ALLOWED_ORIGINS` (comma-separated list of allowed origins).  
Never commit `.env`. Never hardcode secrets.

## Infrastructure routes

Registered explicitly in `bootstrap/app.php` Stage 9. Never auto-discovered.

- `GET /health` — `{status, php, db}`, HTTP 200 or 503
- `GET /admin/logs` — paginated log viewer (requires Auth + Admin)
- `GET /admin/logs/{id}` — log entry detail
- `DELETE /admin/logs` — truncate log file
