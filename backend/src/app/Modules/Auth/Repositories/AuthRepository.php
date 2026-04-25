<?php

namespace App\Modules\Auth\Repositories;

use PDO;
use PDOException;
use App\Exceptions\DatabaseException;

class AuthRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(string $username, string $hashedPassword, int $rol): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = ?');
            $stmt->execute([$username]);

            if ($stmt->fetchColumn() > 0) {
                throw new \Exception('El usuario ya existe en el sistema.');
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO usuarios (usuario, clave, rol) VALUES (?, ?, ?)'
            );
            $stmt->execute([$username, $hashedPassword, $rol]);

            return ['id' => $this->pdo->lastInsertId()];
        } catch (\Exception $e) {
            if ($e instanceof DatabaseException) {
                throw $e;
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function update(string $username, string $hashedPassword, int $id, int $rol): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET usuario = ?, clave = ?, rol = ? WHERE id = ?'
        );
        $stmt->execute([$username, $hashedPassword, $rol, $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateUser(string $username, int $id, int $rol): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET usuario = ?, rol = ? WHERE id = ?'
        );
        $stmt->execute([$username, $rol, $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateToken(int|string $userId, string $token): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET token = ? WHERE id = ?');
        $stmt->execute([$token, $userId]);
    }

    public function clearToken(string $token): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET token = NULL WHERE token = ?');
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    public function findUserByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.usuario, u.rol, r.nombre AS nombre_rol
             FROM usuarios u
             LEFT JOIN roles r ON u.rol = r.id
             WHERE u.token = ?'
        );
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findUserByName(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, clave, rol, tenant_id FROM usuarios WHERE usuario = ?'
        );
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findUserById(int|string $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.usuario, u.rol, r.nombre
             FROM usuarios u
             LEFT JOIN roles r ON u.rol = r.id
             WHERE u.id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findUserPermissions(string $token, string $key): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT rp.estado AS permiso
             FROM usuarios u
             LEFT JOIN roles_permisos rp ON rp.rol = u.rol
             LEFT JOIN permisos p ON rp.permiso = p.id
             WHERE u.token = ? AND p.key = ?'
        );
        $stmt->execute([$token, $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? ['permiso' => $result['permiso']] : ['permiso' => 0];
    }
}
