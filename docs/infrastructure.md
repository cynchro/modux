# Config, logging, paginación, migraciones y testing

> Parte del manual de [Modux](../README.md). Volvé al [índice de documentación](../README.md#documentación).

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

