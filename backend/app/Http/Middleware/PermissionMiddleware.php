<?php

namespace App\Http\Middleware;

use PDO;
use App\Support\Request;
use App\Support\Response;
use App\Exceptions\AuthException;
use App\Exceptions\ForbiddenException;
use App\Support\Contracts\MiddlewareInterface;

class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private PDO $pdo, private string $permission = '')
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = $request->user();

        if (!$user) {
            throw new AuthException('Unauthenticated.');
        }

        $rolId = (int) ($user['rol'] ?? 0);

        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM roles_permisos rp
             JOIN permisos p ON rp.permiso_id = p.id
             WHERE rp.rol_id = ? AND p.key = ?
             LIMIT 1'
        );
        $stmt->execute([$rolId, $this->permission]);

        if (!$stmt->fetch()) {
            throw new ForbiddenException("Permission [{$this->permission}] required.");
        }

        return $next($request);
    }
}
