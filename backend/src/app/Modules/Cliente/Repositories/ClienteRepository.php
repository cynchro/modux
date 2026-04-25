<?php

namespace App\Modules\Cliente\Repositories;

use PDO;
use App\Exceptions\NotFoundException;

class ClienteRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function findAll(string $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE tenant_id = ?');
        $stmt->execute([$tenantId]);
        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed> */
    public function findById(int $id, string $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$id, $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Cliente', $id);
        }

        return $row;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     *
     * TODO: replace with your actual columns:
     *   $stmt = $this->pdo->prepare('INSERT INTO clientes (col1, col2, tenant_id) VALUES (?, ?, ?)');
     *   $stmt->execute([$data['col1'], $data['col2'], $tenantId]);
     *   return $this->findById((int) $this->pdo->lastInsertId(), $tenantId);
     */
    public function create(array $data, string $tenantId): array
    {
        throw new \RuntimeException('create() not implemented yet.');
    }

    /**
     * @param array<string, mixed> $data
     *
     * TODO: replace with your actual columns:
     *   $stmt = $this->pdo->prepare('UPDATE clientes SET col1 = ? WHERE id = ? AND tenant_id = ?');
     *   $stmt->execute([$data['col1'], $id, $tenantId]);
     *   return $stmt->rowCount() > 0;
     */
    public function update(int $id, array $data, string $tenantId): bool
    {
        throw new \RuntimeException('update() not implemented yet.');
    }

    public function delete(int $id, string $tenantId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM clientes WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$id, $tenantId]);
        return $stmt->rowCount() > 0;
    }
}
