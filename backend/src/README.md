# Modux

A lightweight, dependency-injection-first PHP framework organized as a modular monolith. Each business domain lives in its own self-contained module. No facades, no magic statics, no hidden globals — every dependency is explicit and injected.

**Best for:** teams that want full control over their codebase, clear request lifecycles, and testable code without learning a large framework's conventions.

---

## At a glance

```
Request → Kernel → Global pipeline (CORS, SecurityHeaders, Logger)
                 → Route middlewares (Auth, Admin, Tenant)
                 → Controller (typed injection via reflection)
                 → Response (always JSON or HTML, never echo+exit)
```

- **Zero magic** — no facades, no service locator calls in business code
- **PSR-11 container** with autowiring
- **PSR-3 structured logger** (JSON to file or stderr)
- **Middleware pipeline** composable per-route and per-group
- **FormRequest** pattern: validates on construction, throws automatically
- **Exception hierarchy** → automatic JSON HTTP responses
- **Multi-tenancy ready** via `TenantMiddleware` + JWT `tenant_id` claim
- **Versioned migrations** with `php modux migrate`
- **81 unit tests**, phpstan level 6, phpcs PSR-12, GitHub Actions CI

---

## Requirements

- PHP 8.2+
- MySQL 8.0+ (or any PDO-compatible database)
- Composer

---

## Quick start

```bash
git clone https://github.com/your-org/modux.git
cd modux/backend/src

composer install

cp .env.example .env
# Edit .env — minimum required:
#   JWT_SECRET=<run: php -r "echo bin2hex(random_bytes(32));"> 
#   DB_HOST, DB_NAME, DB_USER, DB_PASS

php modux migrate       # create base tables
php modux migrate       # seed default admin user (run seeders/RolesUsersSeeder.php separately)

php -S localhost:8080 -t public/
```

```bash
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"admin@admin.com","clave":"admin123"}'
```

```json
{
  "success": true,
  "response": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGci..."
  }
}
```

---

## CLI — `php modux`

The framework ships with a CLI for scaffolding and database management.

```
php modux make:module <Name>      Scaffold a complete module (7 files)
php modux make:migration <name>   Create a versioned migration file
php modux migrate                 Run all pending migrations
php modux routes                  List every registered route
```

### `make:module`

```bash
php modux make:module Cliente
```

Generates `app/Modules/Cliente/` with:

```
Controllers/ClienteController.php
Repositories/ClienteRepository.php
Services/ClienteService.php
Requests/CreateClienteRequest.php
Requests/UpdateClienteRequest.php
ClienteServiceProvider.php
routes.php
```

### `make:migration`

```bash
php modux make:migration create_clientes_table
# → migrations/0002_create_clientes_table.php
```

Files are numbered sequentially (`0001_`, `0002_`, ...). Each migration exposes `up(PDO)` and `down(PDO)`.

### `migrate`

```bash
php modux migrate
```

```
  migrated   0001_create_base_tables.php
  skipped    0002_create_clientes_table.php   ← already ran

  1 migration(s) ran.
```

Tracks which migrations have run in a `migrations` table. Safe to run multiple times.

### `routes`

```bash
php modux routes
```

```
  METHOD    URI                     HANDLER                    MIDDLEWARES
  ──────────────────────────────────────────────────────────────────────────
  GET       /auth/login             AuthController@login
  POST      /auth/logout            AuthController@logout      AuthMiddleware
  GET       /clientes               ClienteController@index    AuthMiddleware
  GET       /clientes/{id}          ClienteController@show     AuthMiddleware
  DELETE    /usuarios/{id}          UsuarioController@delete   AuthMiddleware, AdminMiddleware
```

Does not require a database connection.

---

## Project structure

```
backend/src/
├── app/
│   ├── Config/         # Database connection bridge (PDO singleton)
│   ├── Exceptions/     # Exception hierarchy + global JSON handler
│   ├── Helpers/        # PaginatorHelper, EmailHelper
│   ├── Http/
│   │   └── Middleware/ # CorsMiddleware, AuthMiddleware, AdminMiddleware,
│   │                   # TenantMiddleware, SecurityHeadersMiddleware,
│   │                   # RequestLoggerMiddleware
│   ├── Modules/        # Business domains (one directory per module)
│   │   └── {Name}/
│   │       ├── Controllers/
│   │       ├── Repositories/
│   │       ├── Requests/       # Extend FormRequest
│   │       ├── Services/
│   │       ├── {Name}ServiceProvider.php
│   │       └── routes.php
│   └── Support/        # Framework core
│       ├── Config.php, Container.php, FormRequest.php
│       ├── Kernel.php, Logger.php, Pipeline.php
│       ├── Request.php, Response.php, Router.php
│       └── Validator.php
├── modux               # CLI entry point
├── bootstrap/
│   ├── app.php         # Boot sequence (env → container → logger → DB → providers)
│   └── test.php        # Test bootstrap (no HTTP dispatch)
├── config/             # app.php, auth.php, cors.php, database.php, logging.php, mail.php
├── migrations/         # Versioned migration files (0001_*.php, 0002_*.php, ...)
├── seeders/            # Data seeders
├── public/index.php    # 3-line entry point
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Creating a module

```bash
php modux make:module Producto
```

Then register the provider in `bootstrap/app.php`:

```php
$providers = [
    // ...existing providers...
    App\Modules\Producto\ProductoServiceProvider::class,
];
```

The generator produces ready-to-use files. The only things to fill in manually:

1. **Repository** — replace the `create()` and `update()` SQL stubs with your actual columns
2. **Requests** — add validation rules
3. **Migration** — `php modux make:migration create_productos_table`

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
        return (array) $this->pdo->query('SELECT * FROM productos')->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed> */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Producto', $id);  // → HTTP 404
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

    public function getAll(): array  { return $this->repository->findAll(); }
    public function get(int $id): array { return $this->repository->findById($id); }
    public function create(array $data): array { return $this->repository->create($data); }
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

    public function create(CreateProductoRequest $request): Response
    {
        // $request->validated() returns only the fields declared in rules()
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
}
```

---

## Routing

### Individual routes

```php
// Public
$router->post('/auth/login', [AuthController::class, 'login']);

// With explicit middlewares
$router->get('/usuarios/{id}', [UsuarioController::class, 'show'],
    [AuthMiddleware::class]);

$router->delete('/usuarios/{id}', [UsuarioController::class, 'delete'],
    [AuthMiddleware::class, AdminMiddleware::class]);
```

### Route groups — share middlewares across routes

```php
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\AdminMiddleware;

// All routes inside inherit AuthMiddleware
$router->group([AuthMiddleware::class], function ($router) {
    $router->get('/productos',      [ProductoController::class, 'index']);
    $router->post('/productos',     [ProductoController::class, 'create']);
    $router->put('/productos/{id}', [ProductoController::class, 'update']);
});

// Nested groups merge middlewares
$router->group([AuthMiddleware::class], function ($router) {
    $router->group([AdminMiddleware::class], function ($router) {
        $router->get('/admin/roles', [AdminController::class, 'roles']);
        $router->get('/admin/logs',  [AdminController::class, 'logs']);
    });
});
```

Route parameters are extracted automatically and available via `$request->route('id')`.

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
            'nombre' => 'required|min:2|max:100',
            'precio' => 'required|integer',
            'activo' => 'boolean',
            'tipo'   => 'required|in:fisico,digital',
        ];
    }
}
```

### `all()` vs `validated()`

```php
// Request body: {"nombre": "Mesa", "precio": 150, "admin": true, "_csrf": "xyz"}
// Rules: {nombre, precio}

$request->all()        // {"nombre": "Mesa", "precio": 150, "admin": true, "_csrf": "xyz"}
$request->validated()  // {"nombre": "Mesa", "precio": 150}  ← only declared fields
```

Always prefer `validated()` in controllers — it prevents over-posting by design.

### Validation rules

| Rule | Example | Description |
|---|---|---|
| `required` | `required` | Field must be present and non-empty |
| `email` | `email` | Must be a valid email address |
| `min:N` | `min:6` | Minimum string length |
| `max:N` | `max:255` | Maximum string length |
| `integer` | `integer` | Must be an integer |
| `boolean` | `boolean` | Must be true/false/0/1 |
| `in:a,b,c` | `in:admin,user` | Must be one of the listed values |
| `nullable` | `nullable` | Skip validation if field is absent or empty |
| `numeric` | `numeric` | Must be numeric (int or float) |
| `confirmed` | `confirmed` | Must match a sibling field named `{field}_confirmation` |

Rules are composable with `|`:

```php
'email' => 'required|email|max:255',
'rol'   => 'nullable|in:1,2,3',
```

---

## Migrations

```bash
# Create a new migration
php modux make:migration create_productos_table
# → migrations/0002_create_productos_table.php

# Run all pending migrations
php modux migrate
```

Each migration file returns an anonymous class with `up()` and `down()`:

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
                created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS productos');
    }
};
```

The `migrate` command tracks ran migrations in a `migrations` table and skips already-applied files. Safe to run on every deploy.

---

## Exceptions → HTTP responses

Throw a typed exception anywhere — the global handler converts it to JSON automatically.

```php
throw new AuthException('Invalid credentials.');       // 401
throw new ForbiddenException('Admin only.');           // 403
throw new NotFoundException('Producto', $id);          // 404 — "Producto not found: 42"
throw new ValidationException(['campo' => ['msg']]);   // 422
throw new DatabaseException('Query failed.');          // 500 (message hidden in prod)
```

Example 404 response:

```json
{
  "success": false,
  "error": "Producto not found: 42"
}
```

Example 422 response:

```json
{
  "success": false,
  "error": "Validation failed.",
  "errors": {
    "email": ["email is required.", "email must be a valid email address."],
    "precio": ["precio must be an integer."]
  }
}
```

---

## Middleware

Middlewares implement `MiddlewareInterface` and are composable per-route or per-group.

| Middleware | Trigger | Effect |
|---|---|---|
| `CorsMiddleware` | Every request | Sets CORS headers; handles OPTIONS preflight |
| `SecurityHeadersMiddleware` | Every request | X-Frame-Options, X-Content-Type-Options, etc. |
| `RequestLoggerMiddleware` | Every request | Logs method, URI, status, duration (JSON) |
| `AuthMiddleware` | Protected routes | Decodes JWT, sets `$request->user()` |
| `AdminMiddleware` | Admin routes | Checks `user['rol'] === 1`, throws 403 otherwise |
| `TenantMiddleware` | Multi-tenant routes | Reads `tenant_id` from JWT, sets `$request->tenantId()` |

### Writing a middleware

```php
namespace App\Http\Middleware;

use App\Support\Request;
use App\Support\Response;
use App\Support\Contracts\MiddlewareInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // check rate limit...
        return $next($request);
    }
}
```

---

## Multi-tenancy

The framework ships with first-class multi-tenancy support via JWT claims.

**How it works:**

1. `usuarios` table has a `tenant_id CHAR(36)` column (FK → `tenants.id`)
2. On login, `tenant_id` is embedded in the JWT payload
3. `TenantMiddleware` validates and exposes it on the request

```php
$router->group([AuthMiddleware::class, TenantMiddleware::class], function ($router) {
    $router->get('/productos', [ProductoController::class, 'index']);
});
```

```php
public function index(Request $request): Response
{
    $tenantId = $request->tenantId();
    return Response::success($this->service->getAllForTenant($tenantId));
}
```

---

## Config

```php
Config::get('auth.jwt_secret');        // config/auth.php → jwt_secret
Config::get('app.debug', false);       // with default
Config::get('cors.allowed_origins');   // array from config/cors.php
```

---

## Pagination

`PaginatorHelper` wraps any raw SQL query and reads `page` / `perPage` from the request automatically.

```php
// In a repository
public function list(): array
{
    $paginator = new PaginatorHelper($this->pdo, 'SELECT * FROM productos WHERE activo = 1');
    return $paginator->getPaginatedResults();
}
```

Query parameters accepted:

| Param | Default | Description |
|---|---|---|
| `page` | `1` | Current page |
| `perPage` | `10` | Items per page |
| `paginate` | `true` | Set to `false` to return all results |

Response shape:

```json
{
  "status": 200,
  "total": 42,
  "cantidad_por_pagina": 10,
  "pagina": 2,
  "cantidad_total": 42,
  "results": [...]
}
```

LIMIT and OFFSET are bound via PDO prepared statements — `perPage` and `page` come from validated integer inputs only.

---

## Logging

```php
public function __construct(
    private ProductoRepository $repository,
    private Logger $logger,
) {}

public function delete(int $id): void
{
    $this->logger->info('Deleting product', ['id' => $id]);
    $this->repository->delete($id);
}
```

Output — structured JSON to `storage/logs/app.log`:

```json
{"timestamp":"2026-04-22T15:30:00+00:00","level":"info","message":"Deleting product","context":{"id":42}}
```

Log levels: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`

If the log file cannot be written (permission error, missing directory), the Logger falls back to `STDERR` automatically — no silent failures.

---

## Testing

```bash
composer test      # PHPUnit
composer lint      # phpcs PSR-12
composer analyse   # phpstan level 6
```

### Unit tests — mock repositories, no DB

```php
class ProductoServiceTest extends UnitTestCase
{
    protected function setUp(): void
    {
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

### Feature tests — full HTTP dispatch, real DB

```php
class AuthFeatureTest extends FeatureTestCase
{
    public function test_login_returns_token(): void
    {
        $response = $this->post('/auth/login', [
            'usuario' => 'admin@admin.com',
            'clave'   => 'admin123',
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('token', $response['response']);
    }
}
```

---

## Environment variables

Copy `.env.example` to `.env`. Required at boot:

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
| `APP_DEBUG` | `false` | Exposes exception details in JSON responses |
| `JWT_TTL` | `86400` | Token lifetime in seconds |
| `JWT_ALGO` | `HS256` | JWT signing algorithm |
| `LOG_CHANNEL` | `file` | `file` or `stderr` |
| `LOG_LEVEL` | `debug` | Minimum log level |
| `CORS_ALLOWED_ORIGINS` | _(none)_ | Comma-separated list of allowed origins |
| `MAIL_HOST`, `MAIL_PORT`, etc. | — | SMTP credentials for EmailHelper |

---

## Why not Laravel?

| | Modux | Laravel |
|---|---|---|
| Dependencies | 5 (jwt, mailer, dotenv, psr/log, psr/container) | 30+ |
| Request lifecycle | Readable in 5 files | Spread across 50+ |
| DI | Explicit constructor injection | Facades + service locator |
| Magic | None | `Auth::user()`, `DB::table()`, `Cache::get()`, ... |
| ORM | Raw PDO | Eloquent |
| Queue / Events | Not included | Full system |
| Validation rules | ~8 essential | 50+ |
| Learning curve | Low — it's just PHP | High — learn the framework |
| Suited for | Controlled APIs, internal tools, learning | Full-featured web apps |

If you need Eloquent, queues, broadcasting, or a full admin panel — use Laravel.  
If you want to understand exactly what happens on every line of every request — use this.

---

## License

MIT
