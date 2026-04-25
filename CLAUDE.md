# Modux — Developer Reference

> Quick reference for AI assistants and developers. Full documentation is in `README.md`.

## Architecture Overview

Production-ready PHP modular monolith. Each domain lives in `app/Modules/{Name}/`. The framework provides routing, PSR-11 DI, middleware pipeline, validation, JWT auth, multi-tenancy, and structured logging.

```
backend/src/
├── app/
│   ├── Config/             # PDO singleton bridge (legacy compat)
│   ├── Exceptions/         # Exception hierarchy + global JSON handler
│   ├── Helpers/            # PaginatorHelper, EmailHelper
│   ├── Http/
│   │   ├── Controllers/    # Infrastructure controllers (HealthController)
│   │   └── Middleware/     # CorsMiddleware, RequestSizeLimitMiddleware,
│   │                       # SecurityHeadersMiddleware, RequestLoggerMiddleware,
│   │                       # AuthMiddleware, AdminMiddleware, TenantMiddleware
│   ├── Modules/            # Business domains
│   │   └── {Name}/
│   │       ├── Controllers/
│   │       ├── Repositories/
│   │       ├── Requests/       # Extend FormRequest
│   │       ├── Services/
│   │       ├── {Name}ServiceProvider.php
│   │       └── routes.php
│   └── Support/            # Framework core
│       ├── Config.php, Container.php (PSR-11), FormRequest.php
│       ├── JWTConfig.php, Kernel.php, Logger.php (PSR-3), Pipeline.php
│       ├── Request.php, Response.php, Router.php
│       ├── ServiceProvider.php, Validator.php
│       └── Contracts/      # MiddlewareInterface, ServiceProviderInterface
├── bootstrap/
│   ├── app.php             # 9-stage boot (env → container → logger → DB → providers → infra routes)
│   └── test.php            # Test bootstrap (no HTTP dispatch)
├── config/                 # app.php, auth.php, cors.php, database.php, logging.php, mail.php
├── migrations/             # 0001_*.php, 0002_*.php (tracked in `migrations` table)
├── public/index.php        # 3-line entry point
└── tests/
    ├── Feature/            # Full HTTP, real DB, transaction rollback
    └── Unit/               # Mocked repos, no DB
```

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

### Adding a module

```bash
php modux make:module <Name>
```

Register in `bootstrap/app.php` under `$providers`. Each provider has `register()` (bindings) and `boot()` (loads routes). Two-pass: all `register()` runs before any `boot()`.

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

Opt-in. Add `TenantMiddleware` to routes. Reads `tenant_id` from JWT payload, sets `$request->tenantId()`. Repositories accept `?string $tenantId` and scope `WHERE tenant_id = ?` when non-null. Without `TenantMiddleware`, tenantId is null and no scoping happens.

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

## Testing

```bash
composer test      # PHPUnit (119 tests)
composer lint      # phpcs PSR-12
composer analyse   # phpstan level 6
```

`UnitTestCase` provides `makeRequest(?array $user, ?string $tenantId): Request`.  
`FeatureTestCase` provides `post()`, `get()`, `put()`, `delete()` helpers + DB transaction rollback.  
Inject JSON body in feature tests: `Request::setTestInputStream(json_encode($data))`.

## Environment Variables

Required at boot: `JWT_SECRET` (min 32 chars), `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.  
Never commit `.env`. Never hardcode secrets.

## Infrastructure routes

- `GET /health` — returns `{status, php, db}`, HTTP 200 or 503
