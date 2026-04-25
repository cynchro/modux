<?php

namespace App\Modules\Tenant\Repositories;

use PDO;
use App\Exceptions\NotFoundException;

class TenantRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, nombre FROM tenants ORDER BY nombre');
        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed> */
    public function findById(string $id): array
    {
        $stmt = $this->pdo->prepare('SELECT id, nombre FROM tenants WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Tenant', $id);
        }

        return $row;
    }

    public function create(string $nombre): string
    {
        $id   = $this->uuid4();
        $stmt = $this->pdo->prepare('INSERT INTO tenants (id, nombre) VALUES (?, ?)');
        $stmt->execute([$id, $nombre]);
        return $id;
    }

    public function update(string $id, string $nombre): bool
    {
        $stmt = $this->pdo->prepare('UPDATE tenants SET nombre = ? WHERE id = ?');
        $stmt->execute([$nombre, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tenants WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    private function uuid4(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
