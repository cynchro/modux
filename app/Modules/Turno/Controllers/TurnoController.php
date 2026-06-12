<?php

namespace App\Modules\Turno\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Modules\Turno\Services\TurnoService;
use App\Modules\Turno\Requests\CreateTurnoRequest;
use App\Modules\Turno\Requests\UpdateTurnoRequest;

class TurnoController
{
    public function __construct(private TurnoService $service)
    {
    }

    public function index(Request $request): Response
    {
        $tenantId = (string) $request->tenantId();
        return Response::success($this->service->getAll($tenantId));
    }

    public function show(Request $request): Response
    {
        $id       = (int) $request->route('id');
        $tenantId = (string) $request->tenantId();
        return Response::success($this->service->get($id, $tenantId));
    }

    public function create(Request $request, CreateTurnoRequest $validated): Response
    {
        $tenantId = (string) $request->tenantId();

        $result = $this->service->create(
            $tenantId,
            (int) $validated->input('cliente_id'),
            (string) $validated->input('servicio'),
            (string) $validated->input('fecha_hora'),
            (int) $validated->input('duracion_min'),
        );

        return Response::success($result, 201);
    }

    public function update(Request $request, UpdateTurnoRequest $validated): Response
    {
        $id       = (int) $request->route('id');
        $tenantId = (string) $request->tenantId();

        $result = $this->service->update(
            $id,
            $tenantId,
            (string) $validated->input('servicio'),
            (string) $validated->input('fecha_hora'),
            (int) $validated->input('duracion_min'),
            (string) $validated->input('estado'),
        );

        return Response::success(['updated' => $result]);
    }

    public function delete(Request $request): Response
    {
        $id       = (int) $request->route('id');
        $tenantId = (string) $request->tenantId();
        $this->service->delete($id, $tenantId);
        return Response::success(['message' => 'Turno deleted.']);
    }
}
