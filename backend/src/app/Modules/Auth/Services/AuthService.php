<?php

namespace App\Modules\Auth\Services;

use App\Exceptions\AuthException;
use App\Support\JWTConfig;
use App\Modules\Auth\Repositories\AuthRepository;

class AuthService
{
    public function __construct(private AuthRepository $repository)
    {
    }

    public function register(array $data): array
    {
        $hashed = password_hash($data['clave'], PASSWORD_BCRYPT);
        return $this->repository->create($data['usuario'], $hashed, (int) ($data['rol'] ?? 0));
    }

    public function update(array $data): bool
    {
        $hashed = password_hash($data['clave'], PASSWORD_BCRYPT);
        return $this->repository->update(
            $data['usuario'],
            $hashed,
            (int) $data['id'],
            (int) ($data['rol'] ?? 0)
        );
    }

    public function updateUser(array $data): bool
    {
        return $this->repository->updateUser(
            $data['usuario'],
            (int) $data['id'],
            (int) ($data['rol'] ?? 0)
        );
    }

    public function login(array $data): string
    {
        $user = $this->repository->findUserByName($data['usuario']);

        if (!$user || !password_verify($data['clave'], $user['clave'])) {
            throw new AuthException('Invalid credentials.', 401);
        }

        $token = JWTConfig::generateToken($user['id'], $user['tenant_id'] ?? null);
        $this->repository->updateToken($user['id'], $token);

        return $token;
    }

    public function logout(string $token): void
    {
        $cleared = $this->repository->clearToken($token);

        if (!$cleared) {
            throw new AuthException('Invalid token or already logged out.', 401);
        }
    }

    public function me(string $token): array
    {
        $user = $this->repository->findUserByToken($token);

        if (!$user) {
            throw new AuthException('Invalid token or user not found.', 401);
        }

        return $user;
    }

    public function permisos(string $token, string $key): array
    {
        return $this->repository->findUserPermissions($token, $key);
    }

    public function impersonate(int $adminId, int $targetId): string
    {
        $admin = $this->repository->findUserById($adminId);

        if (!$admin || (int) ($admin['rol'] ?? 0) !== 1) {
            throw new AuthException('No tienes permisos para suplantar usuarios.', 403);
        }

        $target = $this->repository->findUserById($targetId);

        if (!$target) {
            throw new AuthException('El usuario objetivo no existe.', 404);
        }

        $token = JWTConfig::generateToken($targetId);
        $this->repository->updateToken($targetId, $token);

        return $token;
    }
}
