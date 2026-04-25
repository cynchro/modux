<?php

namespace App\Http\Middleware;

use App\Support\Request;
use App\Support\Response;
use App\Support\Contracts\MiddlewareInterface;
use App\Exceptions\AuthException;

class TenantMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $user = $request->user();

        if (!$user || empty($user['tenant_id'])) {
            throw new AuthException('Tenant context missing from token.', 401);
        }

        $request->setTenantId((string) $user['tenant_id']);

        return $next($request);
    }
}
