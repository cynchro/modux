# Modux — Developer Reference

## Architecture Overview

A production-ready PHP modular monolith framework. Each business domain lives in its own module under `app/Modules/`. The framework provides routing, DI, middleware, validation, and structured logging — with zero non-essential dependencies.

```
backend/src/
├── app/
│   ├── Config/         # Database connection bridge
│   ├── Exceptions/     # Exception hierarchy + global handler
│   ├── Helpers/        # ResponseHelper, PaginatorHelper, EmailHelper, etc.
│   ├── Http/
│   │   └── Middleware/ # CorsMiddleware, AuthMiddleware, AdminMiddleware, etc.
│   ├── Modules/        # Business domain modules
│   │   └── {Name}/
│   │       ├── Controllers/
│   │       ├── Repositories/
│   │       ├── Requests/       (extend FormRequest)
│   │       ├── Services/
│   │       ├── {Name}ServiceProvider.php
│   │       └── routes.php
│   └── Support/        # Framework core
│       ├── Contracts/  # MiddlewareInterface, ServiceProviderInterface
│       ├── Config.php
│       ├── Container.php   (PSR-11)
│       ├── FormRequest.php
│       ├── Kernel.php
│       ├── Logger.php      (PSR-3)
│       ├── Pipeline.php
│       ├── Request.php
│       ├── Response.php
│       ├── Router.php
│       ├── ServiceProvider.php
│       └── Validator.php
├── bootstrap/
│   ├── app.php         # Application boot (env → container → services → kernel)
│   └── test.php        # Test bootstrap (no HTTP dispatch)
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cors.php
│   ├── database.php
│   ├── logging.php
│   └── mail.php
├── migrations/
├── public/
│   └── index.php       # 3-line entry point
├── seeders/
├── storage/logs/
└── tests/
    ├── Feature/
    └── Unit/
```

## Request Lifecycle

```
index.php
  → bootstrap/app.php       (container + services + exception handler)
  → Kernel::handle()        (creates Request, loads routes, dispatches)
  → Router::dispatch()      (matches URI, runs pipeline)
  → Pipeline                (CorsMiddleware → SecurityHeaders → RequestLogger)
  → Route middlewares       (AuthMiddleware?, AdminMiddleware?)
  → Controller::method()    (receives Request, returns Response)
  → Response::send()        (writes headers + JSON body)
```

## Key Conventions

### Adding a new module

```bash
php modux make:module <Name>
```

Each module MUST include:
- `routes.php` — route definitions using `$router`
- `{Name}ServiceProvider.php` — DI bindings, must extend `App\Support\ServiceProvider`

Register the provider in `bootstrap/app.php` under `$providers`.

### Route definition

```php
// Public route
$router->post('/auth/login', [AuthController::class, 'login']);

// Authenticated route
$router->get('/users', [UserController::class, 'index'], [AuthMiddleware::class]);

// Admin-only route
$router->delete('/users/{id}', [UserController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
```

### Controllers return Response

```php
class MyController
{
    public function __construct(private MyService $service) {}

    public function index(Request $request): Response
    {
        return Response::success($this->service->getAll());
    }
}
```

### FormRequest validates on construction

```php
class CreateUserRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'name'  => 'required|min:2|max:100',
        ];
    }
}
```

Throws `ValidationException` (HTTP 422) automatically if validation fails.

### Config access

```php
Config::get('auth.jwt_secret');       // reads config/auth.php → jwt_secret
Config::get('app.debug', false);      // with default
```

### Logger (PSR-3)

```php
$this->logger->info('User logged in', ['user_id' => $id]);
$this->logger->error('DB failed', ['exception' => $e->getMessage()]);
```

Output is structured JSON to `storage/logs/app.log`.

### Exception handling

Throw typed exceptions — the global handler converts them to JSON automatically:

```php
throw new AuthException('Invalid token.');         // → HTTP 401
throw new ForbiddenException('Admin only.');       // → HTTP 403
throw new NotFoundException('User', $id);          // → HTTP 404
throw new ValidationException(['email' => [...]]); // → HTTP 422
throw new DatabaseException('Query failed');       // → HTTP 500
```

## Testing

```bash
composer test          # PHPUnit
composer lint          # phpcs PSR-12
composer analyse       # phpstan level 6
```

Feature tests wrap each test in a DB transaction that auto-rolls back. Unit tests mock repositories — no DB required.

## Environment Variables

Copy `.env.example` → `.env` and fill in values. Required vars (enforced at boot):
- `JWT_SECRET` — minimum 32 chars random string
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

Never commit `.env`. Never hardcode secrets in code.
