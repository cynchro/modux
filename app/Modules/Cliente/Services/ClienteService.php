<?php

namespace App\Modules\Cliente\Services;

use App\Modules\Cliente\Repositories\ClienteRepository;

class ClienteService
{
    public function __construct(private ClienteRepository $repository)
    {
    }

    /** @return list<array<string, mixed>> */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    /** @return array<string, mixed> */
    public function get(int $id): array
    {
        return $this->repository->findById($id);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->repository->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}