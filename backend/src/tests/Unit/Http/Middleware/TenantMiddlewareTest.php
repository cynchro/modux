<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\TenantMiddleware;
use App\Support\Request;
use App\Support\Response;
use App\Exceptions\AuthException;
use Tests\Unit\UnitTestCase;

class TenantMiddlewareTest extends UnitTestCase
{
    private TenantMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new TenantMiddleware();
    }

    public function test_sets_tenant_id_from_user_payload(): void
    {
        $request = $this->makeRequest(['sub' => 42, 'tenant_id' => 'abc-123']);

        $called = false;
        $this->middleware->handle($request, function (Request $req) use (&$called): Response {
            $called = true;
            $this->assertSame('abc-123', $req->tenantId());
            return Response::success([]);
        });

        $this->assertTrue($called);
    }

    public function test_throws_when_user_not_set(): void
    {
        $request = $this->makeRequest(null);

        $this->expectException(AuthException::class);

        $this->middleware->handle($request, fn () => Response::success([]));
    }

    public function test_throws_when_tenant_id_missing_from_payload(): void
    {
        $request = $this->makeRequest(['sub' => 42]);

        $this->expectException(AuthException::class);

        $this->middleware->handle($request, fn () => Response::success([]));
    }

    public function test_throws_when_tenant_id_is_empty_string(): void
    {
        $request = $this->makeRequest(['sub' => 42, 'tenant_id' => '']);

        $this->expectException(AuthException::class);

        $this->middleware->handle($request, fn () => Response::success([]));
    }
}
