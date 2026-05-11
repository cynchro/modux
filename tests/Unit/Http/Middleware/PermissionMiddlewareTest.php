<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\PermissionMiddleware;
use App\Support\Request;
use App\Support\Response;
use App\Exceptions\AuthException;
use App\Exceptions\ForbiddenException;
use Tests\Unit\UnitTestCase;

class PermissionMiddlewareTest extends UnitTestCase
{
    private function mockPdoWithPermission(bool $hasPermission): \PDO
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute');
        $stmt->method('fetch')->willReturn($hasPermission ? ['1' => '1'] : false);

        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        return $pdo;
    }

    public function test_passes_when_permission_exists(): void
    {
        $pdo     = $this->mockPdoWithPermission(true);
        $mw      = new PermissionMiddleware($pdo, 'usuarios.delete');
        $request = $this->makeRequest(['sub' => 1, 'rol' => 1]);

        $called = false;
        $result = $mw->handle($request, function (Request $req) use (&$called): Response {
            $called = true;
            return Response::success();
        });

        $this->assertTrue($called);
        $this->assertSame(200, $result->getStatus());
    }

    public function test_throws_forbidden_when_permission_missing(): void
    {
        $pdo     = $this->mockPdoWithPermission(false);
        $mw      = new PermissionMiddleware($pdo, 'facturas.write');
        $request = $this->makeRequest(['sub' => 1, 'rol' => 2]);

        $this->expectException(ForbiddenException::class);

        $mw->handle($request, fn(Request $r): Response => Response::success());
    }

    public function test_throws_auth_exception_when_no_user(): void
    {
        $pdo     = $this->createMock(\PDO::class);
        $mw      = new PermissionMiddleware($pdo, 'any.permission');
        $request = $this->makeRequest(null);

        $this->expectException(AuthException::class);

        $mw->handle($request, fn(Request $r): Response => Response::success());
    }
}
