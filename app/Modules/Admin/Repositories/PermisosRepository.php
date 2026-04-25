<?php

namespace App\Modules\Admin\Repositories;

use PDO;
use PDOException;

class PermisosRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM permisos');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM permisos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAvailable(int $rolId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id AS id_permiso, p.key
             FROM permisos p
             LEFT JOIN roles_permisos rp ON p.id = rp.permiso AND rp.rol = ?
             WHERE rp.permiso IS NULL'
        );
        $stmt->execute([$rolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findInUse(int $rolId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id AS id_permiso, p.key
             FROM permisos p
             INNER JOIN roles_permisos rp ON p.id = rp.permiso
             WHERE rp.rol = ?'
        );
        $stmt->execute([$rolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignar(int $rolId, int $permisoId): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO roles_permisos (rol, permiso, estado) VALUES (?, ?, ?)'
        );
        $stmt->execute([$rolId, $permisoId, 2]);
    }

    public function desasignar(int $rolId, int $permisoId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM roles_permisos WHERE rol = ? AND permiso = ?'
        );
        $stmt->execute([$rolId, $permisoId]);
    }

    public function createPermiso(string $key, string $descripcion): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO permisos (`key`, descripcion, estado) VALUES (?, ?, ?)'
        );
        $stmt->execute([$key, $descripcion, 0]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updatePermiso(int $id, string $key, string $descripcion, int $estado): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE permisos SET `key` = ?, descripcion = ?, estado = ? WHERE id = ?'
        );
        $stmt->execute([$key, $descripcion, $estado, $id]);
        return $stmt->rowCount() > 0;
    }
}
