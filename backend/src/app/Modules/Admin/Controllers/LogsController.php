<?php

namespace App\Modules\Admin\Controllers;

use App\Helpers\RenderHelper;
use App\Modules\Admin\Services\LogService;

class LogsController
{
    public function index()
    {

        $logService = new LogService();
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

                $paginatedLogs = $logService->getPaginatedLogs($page);
                $paginationData = $logService->getPaginationData();

        return RenderHelper::render('Admin.views.logs',['paginatedLogs'=>$paginatedLogs,'paginationData'=>$paginationData]);
    }

    public function show($id)
    {
        $logService = new LogService();
        $index = isset($id) ? intval($id) : null;
    
        if ($index === null || !isset($logService->getPaginatedLogs(1)[$index])) {
            header('Location: /logs');
            exit;
        }
    
        $logDetail = $logService->getLogDetail($index);
    
        return RenderHelper::render('Admin.views.logsDetail', ['logDetail' => $logDetail]);
    }

    public function delete()
    {
        $logService = new LogService();
        $logService->deleteSelectedLogs($_POST['logs'] ?? []);
        header('Location: ' . $_SERVER['PHP_SELF']);
    }

    public function deleteAll()
    {
        $logService = new LogService();
        $logService->deleteAllLogs();
        header('Location: ' . $_SERVER['PHP_SELF']);
    }

}

