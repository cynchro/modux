# Modux

A lightweight, **dependency-injection-first** PHP **modular-monolith** framework — no magic.
Each business domain lives in its own self-contained module. No facades, no magic statics, no
hidden globals — every dependency is explicit and injected.

**Best for** teams that want full control over their code, a clear request lifecycle, and real
tests without learning a large framework's conventions.

---

## The request lifecycle

```
Request → Kernel → Global pipeline (CORS, RequestSize, SecurityHeaders, Logger)
                 → Router → route middlewares (Auth, Tenant, Scope, Entitlement, Quota)
                 → Controller → Service → Repository → PDO
                 → Response
```

## Quick start

```bash
# 1. Configure the environment
cp .env.example .env          # set JWT_SECRET, DB_HOST, DB_NAME, DB_USER, DB_PASS

# 2. Migrations
php modux migrate

# 3. Server
php -S localhost:8080 -t public
```

```bash
# Login
curl -X POST localhost:8080/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"usuario":"user@example.com","clave":"secret-password"}'

# Health check
curl localhost:8080/health
```

## Performance

Measured on the production image (PHP 8.2 + Apache/mod_php, MySQL 8.0) with ApacheBench at
concurrency 50:

| Endpoint | What it measures | Req/s | p50 | p95 | p99 |
|---|---|---|---|---|---|
| `GET /` | Framework overhead (routing + DI + middleware pipeline) | ~3,520 | 13 ms | 21 ms | 27 ms |
| `GET /health` | Framework + one `SELECT 1` round-trip | ~1,910 | 25 ms | 38 ms | 45 ms |

About **0.28 ms of framework overhead per request**, zero failed requests under load. Numbers
are indicative (single containerized host) and vary with hardware and workload.

## Where to go next

- **[CLI](cli.md)** — `make:module`, `make:migration`, `migrate`, `routes`, `queue:*`…
- **[Modules & Routing](modules.md)** — create a module, route groups, boot sequence.
- **[HTTP](http.md)** — Request/Response, validation, exceptions → HTTP, middleware.
- **[Auth & multi-tenancy](auth-and-tenancy.md)** — JWT, API keys, scopes, webhooks, tenant isolation.
- **[Infrastructure](infrastructure.md)** — config, logger, migrations, **testing**.
- **[Platform](platform.md)** — events, RBAC, entitlements, quotas, job queue.
- **[Optional modules](optional-modules.md)** — AI (LLM + RAG) and Billing (Stripe / Mercado Pago).
