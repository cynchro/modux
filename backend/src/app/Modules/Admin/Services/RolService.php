<?php

namespace App\Modules\Admin\Services;

use App\Exceptions\NotFoundException;
use App\Modules\Admin\Repositories\RolRepository;

class RolService
{
    public function __construct(private RolRepository $repository)
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

    public function create(string $nombre): int
    {
        return $this->repository->create($nombre);
    }

    public function update(int $id, string $nombre, int $estado): bool
    {
        return $this->repository->update($id, $nombre, $estado);
    }
}
