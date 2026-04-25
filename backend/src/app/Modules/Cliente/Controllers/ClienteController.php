<?php

namespace App\Modules\Cliente\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Modules\Cliente\Services\ClienteService;
use App\Modules\Cliente\Requests\CreateClienteRequest;
use App\Modules\Cliente\Requests\UpdateClienteRequest;

class ClienteController
{
    public function __construct(private ClienteService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Response::success($this->service->getAll());
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->route('id');
        return Response::success($this->service->get($id));
    }

    public function create(CreateClienteRequest $request): Response
    {
        $result = $this->service->create($request->all());
        return Response::success($result, 201);
    }

    public function update(UpdateClienteRequest $request): Response
    {
        // route params are merged into FormRequest data by the router
        $id     = (int) $request->input('id');
        $result = $this->service->update($id, $request->all());
        return Response::success(['updated' => $result]);
    }

    public function delete(Request $request): Response
    {
        $id = (int) $request->route('id');
        $this->service->delete($id);
        return Response::success(['message' => 'Cliente deleted.']);
    }
}
