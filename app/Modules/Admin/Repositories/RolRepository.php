<?php

namespace App\Modules\Admin\Repositories;

use PDO;
use PDOException;
use App\Exceptions\NotFoundException;

class RolRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            throw new NotFoundException('Rol', $id);
        }

        return $result;
    }

    public function create(string $nombre): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO roles (nombre, estado) VALUES (?, ?)');
        $stmt->execute([$nombre, 1]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $nombre, int $estado): bool
    {
        $stmt = $this->pdo->prepare('UPDATE roles SET nombre = ?, estado = ? WHERE id = ?');
        $stmt->execute([$nombre, $estado, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE roles SET estado = 0 WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
