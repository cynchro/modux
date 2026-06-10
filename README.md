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

Create a new project from the [Packagist](https://packagist.org/packages/cynchro/modux)
skeleton (recommended):

```bash
composer create-project cynchro/modux my-app
cd my-app
cp .env.example .env
# Edit .env — see Environment Variables
```

> To contribute to the framework itself instead, clone the repo:
> `git clone https://github.com/cynchro/modux.git && cd modux && composer install`

---

## Quick start

```bash
# 1. Configure environment
cp .env.example .env
# Set JWT_SECRET, DB_HOST, DB_NAME, DB_USER, DB_PASS

# 2. Run migrations
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
  "data": { "status": "ok", "php": "8.2.0", "checks": { "db": "ok", "cache": "ok" } }
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

## Documentación

El manual completo vive en [`docs/`](docs/). Esta página es solo el quickstart;
cada tema en profundidad está en su propio archivo:

- [CLI — `php modux`](docs/cli.md) — `make:module`, `make:migration`, `migrate`, `routes`.
- [Módulos, ruteo y arranque](docs/modules.md) — secuencia de boot, crear un módulo (Repository/Service/Controller/ServiceProvider), grupos de rutas.
- [HTTP](docs/http.md) — Request API, Response API, validación de requests, excepciones → HTTP, middleware.
- [Auth & multi-tenancy](docs/auth-and-tenancy.md) — login/refresh/logout, impersonación, API keys, firmas de webhooks, contenedor DI, aislamiento por tenant.
- [Infraestructura](docs/infrastructure.md) — config, logger, paginación, migraciones, testing y quality gate.
- [Plataforma](docs/platform.md) — eventos, RBAC, entitlements, metering/cuotas, transacciones, cola de jobs, health check, variables de entorno.
- [Módulos opcionales](docs/optional-modules.md) — IA (LLM + RAG) y Billing (Stripe / Mercado Pago).

## Performance

Measured on the production image (PHP 8.2 + Apache/mod_php, MySQL 8.0) with ApacheBench at
concurrency 50:

| Endpoint | What it measures | Req/s | p50 | p95 | p99 |
|---|---|---|---|---|---|
| `GET /` | Framework overhead (routing + DI + middleware pipeline) | ~3,520 | 13 ms | 21 ms | 27 ms |
| `GET /health` | Framework + one `SELECT 1` round-trip | ~1,910 | 25 ms | 38 ms | 45 ms |

About **0.28 ms of framework overhead per request**, zero failed requests under load. Numbers
are indicative (single containerized host) and vary with hardware and workload.

---

## License

MIT
