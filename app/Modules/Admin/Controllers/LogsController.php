<?php

namespace App\Modules\Admin\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Exceptions\NotFoundException;
use App\Modules\Admin\Services\LogService;

class LogsController
{
    public function __construct(private LogService $logService)
    {
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->input('page', 1));

        return Response::success([
            'logs'       => $this->logService->getPaginatedLogs($page),
            'pagination' => $this->logService->getPaginationData(),
        ]);
    }

    public function show(Request $request): Response
    {
        $index  = (int) $request->route('id');
        $detail = $this->logService->getLogDetail($index);

        if ($detail === null) {
            throw new NotFoundException('Log', $index);
        }

        return Response::success($detail);
    }

    public function deleteAll(Request $request): Response
    {
        $this->logService->deleteAllLogs();

        return Response::success(['deleted' => true]);
    }
}
