<?php

namespace App\Modules\Usuario\Repositories;

use PDO;
use PDOException;
use App\Helpers\PaginatorHelper;
use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;

class UsuariosRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(): array
    {
        $paginator = new PaginatorHelper($this->pdo, 'SELECT * FROM usuarios');
        return $paginator->getPaginatedResults();
    }

    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new NotFoundException('Usuario', $id);
        }

        return $result;
    }

    public function create(array $data): bool
    {
        // Implement as needed for your domain
        return true;
    }

    public function update(array $data): bool
    {
        // Implement as needed for your domain
        return true;
    }

    public function updateSucursal(int $userId, int $sucursalId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE empleados SET id_sucursal = ? WHERE id_usuario = ?'
        );
        $stmt->execute([$sucursalId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
