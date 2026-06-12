<?php

namespace Tests\Feature;

use App\Support\Auth\PermissionChecker;

/**
 * CRUD del módulo `Turno` contra DB real: ejercita TurnoRepository (incluido el
 * chequeo de solapamiento), la validación de dominio en TurnoService, el RBAC
 * con niveles (PermissionMiddleware) y el pipeline Auth + Tenant.
 */
class TurnoCrudTest extends FeatureTestCase
{
    /** Crea el permiso `turnos` y lo asigna al rol en el nivel dado. */
    private function grantTurnos(int $rol, int $level): void
    {
        $this->pdo->prepare("INSERT INTO permisos (`key`, estado) VALUES ('turnos', 0)")->execute();
        $permisoId = (int) $this->pdo->lastInsertId();
        $this->pdo->prepare('INSERT INTO roles_permisos (rol, permiso, estado) VALUES (?, ?, ?)')
            ->execute([$rol, $permisoId, $level]);
    }

    private function seedCliente(string $tenantId): int
    {
        $this->pdo->prepare('INSERT INTO clientes (nombre, tenant_id) VALUES (?, ?)')
            ->execute(['Cliente PoC', $tenantId]);
        return (int) $this->pdo->lastInsertId();
    }

    private function future(string $modify = '+1 day'): string
    {
        return (new \DateTimeImmutable($modify))->format('Y-m-d H:i:s');
    }

    public function test_full_crud_and_overlap_rule(): void
    {
        $ctx = $this->actingAsUser(); // rol = 1 por defecto
        $this->grantTurnos(1, PermissionChecker::LEVEL_WRITE);
        $auth      = $this->bearer($ctx['token']);
        $clienteId = $this->seedCliente($ctx['tenantId']);
        $start     = $this->future('+1 day');

        // create
        $created = $this->postJson('/turnos', [
            'cliente_id'   => $clienteId,
            'servicio'     => 'Corte de pelo',
            'fecha_hora'   => $start,
            'duracion_min' => 30,
        ], $auth);
        $this->assertSame(201, $created['status']);
        $id = (int) $created['json']['data']['id'];
        $this->assertSame('pendiente', $created['json']['data']['estado']);

        // overlap → 422 (mismo cliente, mismo horario)
        $overlap = $this->postJson('/turnos', [
            'cliente_id'   => $clienteId,
            'servicio'     => 'Otro',
            'fecha_hora'   => $start,
            'duracion_min' => 30,
        ], $auth);
        $this->assertSame(422, $overlap['status']);

        // index
        $list = $this->getJson('/turnos', $auth);
        $this->assertSame(200, $list['status']);
        $this->assertCount(1, $list['json']['data']);

        // update (reprogramar + confirmar)
        $updated = $this->putJson("/turnos/{$id}", [
            'servicio'     => 'Corte de pelo',
            'fecha_hora'   => $this->future('+2 day'),
            'duracion_min' => 45,
            'estado'       => 'confirmado',
        ], $auth);
        $this->assertSame(200, $updated['status']);
        $this->assertTrue($updated['json']['data']['updated']);

        // delete
        $deleted = $this->deleteJson("/turnos/{$id}", $auth);
        $this->assertSame(200, $deleted['status']);

        $missing = $this->getJson("/turnos/{$id}", $auth);
        $this->assertSame(404, $missing['status']);
    }

    public function test_create_rejects_past_date(): void
    {
        $ctx = $this->actingAsUser(); // rol = 1 por defecto
        $this->grantTurnos(1, PermissionChecker::LEVEL_WRITE);
        $auth      = $this->bearer($ctx['token']);
        $clienteId = $this->seedCliente($ctx['tenantId']);

        $res = $this->postJson('/turnos', [
            'cliente_id'   => $clienteId,
            'servicio'     => 'Corte',
            'fecha_hora'   => '2000-01-01 10:00:00',
            'duracion_min' => 30,
        ], $auth);

        $this->assertSame(422, $res['status']);
    }

    public function test_write_requires_write_level(): void
    {
        $ctx = $this->actingAsUser(); // rol = 1 por defecto
        // Solo lectura: puede listar pero no crear.
        $this->grantTurnos(1, PermissionChecker::LEVEL_READ);
        $auth      = $this->bearer($ctx['token']);
        $clienteId = $this->seedCliente($ctx['tenantId']);

        $this->assertSame(200, $this->getJson('/turnos', $auth)['status']);

        $res = $this->postJson('/turnos', [
            'cliente_id'   => $clienteId,
            'servicio'     => 'Corte',
            'fecha_hora'   => $this->future(),
            'duracion_min' => 30,
        ], $auth);
        $this->assertSame(403, $res['status']);
    }

    public function test_requires_authentication(): void
    {
        $this->assertSame(401, $this->getJson('/turnos')['status']);
    }
}
