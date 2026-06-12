<?php

namespace Tests\Unit\Modules\Turno;

use App\Modules\Turno\Services\TurnoService;
use App\Modules\Turno\Repositories\TurnoRepository;
use App\Exceptions\ValidationException;
use Tests\Unit\UnitTestCase;

class TurnoServiceTest extends UnitTestCase
{
    private function future(string $modify = '+1 day'): string
    {
        return (new \DateTimeImmutable($modify))->format('Y-m-d H:i:s');
    }

    public function test_create_rejects_past_date(): void
    {
        $repo = $this->createMock(TurnoRepository::class);
        $repo->expects($this->never())->method('hasOverlap');
        $repo->expects($this->never())->method('create');

        $service = new TurnoService($repo);

        $this->expectException(ValidationException::class);
        $service->create('t1', 5, 'Corte', '2000-01-01 10:00:00', 30);
    }

    public function test_create_rejects_non_positive_duration(): void
    {
        $repo = $this->createMock(TurnoRepository::class);
        $repo->expects($this->never())->method('create');

        $service = new TurnoService($repo);

        $this->expectException(ValidationException::class);
        $service->create('t1', 5, 'Corte', $this->future(), 0);
    }

    public function test_create_rejects_overlap(): void
    {
        $repo = $this->createMock(TurnoRepository::class);
        $repo->method('hasOverlap')->willReturn(true);
        $repo->expects($this->never())->method('create');

        $service = new TurnoService($repo);

        $this->expectException(ValidationException::class);
        $service->create('t1', 5, 'Corte', $this->future(), 30);
    }

    public function test_create_persists_when_valid(): void
    {
        $repo = $this->createMock(TurnoRepository::class);
        $repo->method('hasOverlap')->willReturn(false);
        $repo->expects($this->once())->method('create')->willReturn(['id' => 7, 'estado' => 'pendiente']);

        $service = new TurnoService($repo);

        $result = $service->create('t1', 5, 'Corte', $this->future(), 30);
        $this->assertSame(7, $result['id']);
    }

    public function test_update_to_cancelled_skips_schedule_and_overlap_checks(): void
    {
        $repo = $this->createMock(TurnoRepository::class);
        $repo->method('findById')->willReturn(['id' => 7, 'cliente_id' => 5]);
        $repo->expects($this->never())->method('hasOverlap');
        $repo->expects($this->once())->method('update')->willReturn(true);

        $service = new TurnoService($repo);

        // Fecha en el pasado, pero al cancelar no se valida la agenda.
        $this->assertTrue($service->update(7, 't1', 'Corte', '2000-01-01 10:00:00', 30, 'cancelado'));
    }

    public function test_update_reschedule_checks_overlap_excluding_self(): void
    {
        $repo = $this->createMock(TurnoRepository::class);
        $repo->method('findById')->willReturn(['id' => 7, 'cliente_id' => 5]);
        $repo->expects($this->once())
            ->method('hasOverlap')
            ->with('t1', 5, $this->anything(), 30, 7)
            ->willReturn(false);
        $repo->expects($this->once())->method('update')->willReturn(true);

        $service = new TurnoService($repo);

        $this->assertTrue($service->update(7, 't1', 'Corte', $this->future(), 30, 'confirmado'));
    }
}
