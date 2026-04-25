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
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM clientes');
        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed> */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE id = ?');
        $stmt->execute([$id]);
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
     *   $stmt = $this->pdo->prepare('INSERT INTO clientes (col1, col2) VALUES (?, ?)');
     *   $stmt->execute([$data['col1'], $data['col2']]);
     *   return $this->findById((int) $this->pdo->lastInsertId());
     */
    public function create(array $data): array
    {
        // TODO: replace with your actual columns
        // $stmt = $this->pdo->prepare('INSERT INTO clientes (col1, col2) VALUES (?, ?)');
        // $stmt->execute([$data['col1'], $data['col2']]);
        // return $this->findById((int) $this->pdo->lastInsertId());
        throw new \RuntimeException('create() not implemented yet.');
    }

    /**
     * @param array<string, mixed> $data
     *
     * TODO: replace with your actual columns:
     *   $stmt = $this->pdo->prepare('UPDATE clientes SET col1 = ? WHERE id = ?');
     *   $stmt->execute([$data['col1'], $id]);
     *   return $stmt->rowCount() > 0;
     */
    public function update(int $id, array $data): bool
    {
        // TODO: replace with your actual columns
        // $stmt = $this->pdo->prepare('UPDATE clientes SET col1 = ? WHERE id = ?');
        // $stmt->execute([$data['col1'], $id]);
        // return $stmt->rowCount() > 0;
        throw new \RuntimeException('update() not implemented yet.');
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM clientes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}