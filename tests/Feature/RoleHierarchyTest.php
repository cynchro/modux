<?php

namespace Tests\Feature;

use App\Support\Auth\PermissionChecker;

/**
 * Verifica la herencia de permisos a través de roles.parent_id contra MySQL real.
 */
class RoleHierarchyTest extends FeatureTestCase
{
    private function createPermiso(string $key): int
    {
        $this->pdo->prepare('INSERT INTO permisos (`key`, estado) VALUES (?, 0)')->execute([$key]);
        return (int) $this->pdo->lastInsertId();
    }

    private function createRol(string $nombre, ?int $parentId = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO roles (nombre, parent_id, estado) VALUES (?, ?, 1)');
        $stmt->execute([$nombre, $parentId]);
        return (int) $this->pdo->lastInsertId();
    }

    private function assignPermiso(int $rolId, int $permisoId, int $estado): void
    {
        $this->pdo->prepare('INSERT INTO roles_permisos (rol, permiso, estado) VALUES (?, ?, ?)')
            ->execute([$rolId, $permisoId, $estado]);
    }

    private function checker(): PermissionChecker
    {
        return $this->app->get(PermissionChecker::class);
    }

    public function test_child_role_inherits_parent_permission(): void
    {
        $permiso = $this->createPermiso('reports');
        $parent  = $this->createRol('Manager');
        $child   = $this->createRol('Analyst', $parent);

        $this->assignPermiso($parent, $permiso, PermissionChecker::LEVEL_WRITE);

        // El hijo no tiene asignación directa, pero hereda la del padre.
        $this->assertSame(PermissionChecker::LEVEL_WRITE, $this->checker()->level($child, 'reports'));
        $this->assertTrue($this->checker()->allows($child, 'reports', PermissionChecker::LEVEL_WRITE));
    }

    public function test_child_own_permission_takes_max_over_parent(): void
    {
        $permiso = $this->createPermiso('reports');
        $parent  = $this->createRol('Manager');
        $child   = $this->createRol('Analyst', $parent);

        $this->assignPermiso($parent, $permiso, PermissionChecker::LEVEL_READ);
        $this->assignPermiso($child, $permiso, PermissionChecker::LEVEL_WRITE);

        // El nivel efectivo es el máximo entre rol propio y ancestros.
        $this->assertSame(PermissionChecker::LEVEL_WRITE, $this->checker()->level($child, 'reports'));
    }

    public function test_role_without_ancestor_permission_has_none(): void
    {
        $this->createPermiso('reports');
        $orphan = $this->createRol('Guest');

        $this->assertSame(PermissionChecker::LEVEL_NONE, $this->checker()->level($orphan, 'reports'));
        $this->assertFalse($this->checker()->allows($orphan, 'reports'));
    }

    public function test_inheritance_spans_multiple_levels(): void
    {
        $permiso     = $this->createPermiso('reports');
        $grandparent = $this->createRol('Director');
        $parent      = $this->createRol('Manager', $grandparent);
        $child       = $this->createRol('Analyst', $parent);

        $this->assignPermiso($grandparent, $permiso, PermissionChecker::LEVEL_READ);

        // La herencia sube por toda la cadena, no solo un nivel.
        $this->assertSame(PermissionChecker::LEVEL_READ, $this->checker()->level($child, 'reports'));
    }
}
