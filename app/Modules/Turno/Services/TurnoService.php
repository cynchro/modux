<?php

namespace App\Modules\Turno\Services;

use App\Exceptions\ValidationException;
use App\Modules\Turno\Repositories\TurnoRepository;

class TurnoService
{
    public function __construct(private TurnoRepository $repository)
    {
    }

    /** @return list<array<string, mixed>> */
    public function getAll(string $tenantId): array
    {
        return $this->repository->findAll($tenantId);
    }

    /** @return array<string, mixed> */
    public function get(int $id, string $tenantId): array
    {
        return $this->repository->findById($id, $tenantId);
    }

    /** @return array<string, mixed> */
    public function create(
        string $tenantId,
        int $clienteId,
        string $servicio,
        string $fechaHora,
        int $duracionMin
    ): array {
        $this->guardSchedule($fechaHora, $duracionMin);

        if ($this->repository->hasOverlap($tenantId, $clienteId, $fechaHora, $duracionMin, null)) {
            throw new ValidationException(
                ['fecha_hora' => ['El turno se solapa con otro del mismo cliente.']]
            );
        }

        return $this->repository->create($tenantId, $clienteId, $servicio, $fechaHora, $duracionMin);
    }

    public function update(
        int $id,
        string $tenantId,
        string $servicio,
        string $fechaHora,
        int $duracionMin,
        string $estado
    ): bool {
        $turno     = $this->repository->findById($id, $tenantId);
        $clienteId = (int) $turno['cliente_id'];

        // Un turno cancelado no ocupa horario: no se revalida agenda ni solapamiento.
        if ($estado !== 'cancelado') {
            $this->guardSchedule($fechaHora, $duracionMin);

            if ($this->repository->hasOverlap($tenantId, $clienteId, $fechaHora, $duracionMin, $id)) {
                throw new ValidationException(
                    ['fecha_hora' => ['El turno se solapa con otro del mismo cliente.']]
                );
            }
        }

        return $this->repository->update($id, $tenantId, $servicio, $fechaHora, $duracionMin, $estado);
    }

    public function delete(int $id, string $tenantId): bool
    {
        return $this->repository->delete($id, $tenantId);
    }

    private function guardSchedule(string $fechaHora, int $duracionMin): void
    {
        try {
            $start = new \DateTimeImmutable($fechaHora);
        } catch (\Exception) {
            throw new ValidationException(['fecha_hora' => ['Fecha/hora inválida.']]);
        }

        if ($start <= new \DateTimeImmutable('now')) {
            throw new ValidationException(['fecha_hora' => ['El turno debe ser en el futuro.']]);
        }

        if ($duracionMin <= 0) {
            throw new ValidationException(['duracion_min' => ['La duración debe ser mayor a 0.']]);
        }
    }
}
