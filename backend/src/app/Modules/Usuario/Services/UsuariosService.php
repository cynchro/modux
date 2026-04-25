<?php

namespace App\Modules\Usuario\Services;

use App\Exceptions\NotFoundException;
use App\Modules\Usuario\Repositories\UsuariosRepository;

class UsuariosService
{
    public function __construct(private UsuariosRepository $repository)
    {
    }

    public function getAll(): array
    {
        return $this->repository->find();
    }

    public function get(int $id): array
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): bool
    {
        return $this->repository->create($data);
    }

    public function update(array $data): bool
    {
        return $this->repository->update($data);
    }

    public function updateSucursal(int $userId, int $sucursalId): bool
    {
        return $this->repository->updateSucursal($userId, $sucursalId);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
