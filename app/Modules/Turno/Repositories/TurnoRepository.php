<?php

namespace App\Modules\Turno\Repositories;

use PDO;
use App\Exceptions\NotFoundException;

class TurnoRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function findAll(string $tenantId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM turnos WHERE tenant_id = ? ORDER BY fecha_hora'
        );
        $stmt->execute([$tenantId]);
        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed> */
    public function findById(int $id, string $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM turnos WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$id, $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException('Turno', $id);
        }

        return $row;
    }

    /** @return array<string, mixed> */
    public function create(
        string $tenantId,
        int $clienteId,
        string $servicio,
        string $fechaHora,
        int $duracionMin
    ): array {
        $stmt = $this->pdo->prepare(
            'INSERT INTO turnos (tenant_id, cliente_id, servicio, fecha_hora, duracion_min, estado)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$tenantId, $clienteId, $servicio, $fechaHora, $duracionMin, 'pendiente']);

        return $this->findById((int) $this->pdo->lastInsertId(), $tenantId);
    }

    public function update(
        int $id,
        string $tenantId,
        string $servicio,
        string $fechaHora,
        int $duracionMin,
        string $estado
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE turnos SET servicio = ?, fecha_hora = ?, duracion_min = ?, estado = ?
             WHERE id = ? AND tenant_id = ?'
        );
        $stmt->execute([$servicio, $fechaHora, $duracionMin, $estado, $id, $tenantId]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, string $tenantId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM turnos WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$id, $tenantId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * ¿Existe otro turno no cancelado del mismo cliente cuyo intervalo se solape
     * con [$fechaHora, $fechaHora + $duracionMin)? `$excludeId` excluye el propio
     * turno al reprogramar.
     *
     * Dos intervalos se solapan si: inicio_a < fin_b  Y  inicio_b < fin_a.
     */
    public function hasOverlap(
        string $tenantId,
        int $clienteId,
        string $fechaHora,
        int $duracionMin,
        ?int $excludeId = null
    ): bool {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM turnos
             WHERE tenant_id = ?
               AND cliente_id = ?
               AND estado <> \'cancelado\'
               AND (? IS NULL OR id <> ?)
               AND fecha_hora < DATE_ADD(?, INTERVAL ? MINUTE)
               AND DATE_ADD(fecha_hora, INTERVAL duracion_min MINUTE) > ?
             LIMIT 1'
        );
        $stmt->execute([$tenantId, $clienteId, $excludeId, $excludeId, $fechaHora, $duracionMin, $fechaHora]);

        return (bool) $stmt->fetch();
    }
}
