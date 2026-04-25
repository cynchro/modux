<?php

namespace App\Modules\Admin\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Helpers\RenderHelper;
use App\Modules\Admin\Services\LogService;

class LogsController
{
    public function __construct(private LogService $logService)
    {
    }

    public function index(Request $request): Response
    {
        $page          = max(1, (int) $request->input('page', 1));
        $paginatedLogs = $this->logService->getPaginatedLogs($page);
        $paginationData = $this->logService->getPaginationData();

        return RenderHelper::render('Admin.views.logs', [
            'paginatedLogs'  => $paginatedLogs,
            'paginationData' => $paginationData,
        ]);
    }

    public function show(Request $request): Response
    {
        $index     = (int) $request->route('id');
        $logDetail = $this->logService->getLogDetail($index);

        if ($logDetail === null) {
            return Response::redirect('/admin/logs');
        }

        return RenderHelper::render('Admin.views.logsDetail', ['logDetail' => $logDetail]);
    }

    public function deleteAll(Request $request): Response
    {
        $this->logService->deleteAllLogs();
        return Response::redirect('/admin/logs');
    }
}
