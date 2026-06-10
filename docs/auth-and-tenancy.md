# Auth, API keys, webhooks, DI y multi-tenancy


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

**El anti-replay exige un store operativo.** El nonce vive en `CacheInterface`, cuyo binding
por defecto es `ApcuCache`. Si el cache no es operativo (`available() === false`, p. ej. APCu
deshabilitado), `verify()` **falla cerrado** — rechaza la firma en vez de aceptarla sin
protección — y se loguea un aviso al bootear. Además, **APCu es por proceso**: en un deploy
multi-instancia, vinculá `CacheInterface` a un store compartido (Redis/DB) implementando la
interfaz, o un reenvío podría no detectarse al caer en otra instancia. El esquema HMAC no
cambia. Ver `docs/adr/0001-saas-identity-entitlements-billing.md` §1.3.

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

