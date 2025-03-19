<?php

namespace App\Modules\Logs\Services;

class LogService
{
    private $logFile;
    private $logs = [];
    private $logsPerPage = 15;
    private $totalLogs;
    private $totalPages;

    public function __construct()
    {
        $this->logFile = dirname(__DIR__, 4) . '/public/storage/logs/app.log';
        $this->loadLogs();
    }

    private function loadLogs()
    {
        if (file_exists($this->logFile)) {
            $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $currentLog = [];
            foreach ($lines as $line) {
                // Detectar el inicio de un log (por ejemplo, el formato de fecha y nivel)
                if (preg_match('/^\[(.*?)\] \[(.*?)\]/', $line, $matches)) {
                    // Si ya estamos procesando un log, lo agregamos a la lista
                    if (!empty($currentLog)) {
                        $this->logs[] = $currentLog;
                    }

                    // Comenzamos un nuevo log con información básica
                    $currentLog = [
                        'date' => $matches[1],
                        'level' => $matches[2],
                        'message' => strlen($line) > 100 ? substr($line, 0, 100) . '...' : $line, // Resumen del mensaje
                        'full_message' => $line // Guardar el mensaje completo como texto largo
                    ];

                } else {
                    // Si no es una nueva entrada, agregamos la línea al log actual
                    if (!empty($currentLog)) {
                        $currentLog['full_message'] .= "\n" . $line;
                    }
                }
            }

            // Agregar el último log procesado
            if (!empty($currentLog)) {
                $this->logs[] = $currentLog;
            }

            // Ordenar por fecha (más reciente primero)
            usort($this->logs, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }

        $this->totalLogs = count($this->logs);
        $this->totalPages = ceil($this->totalLogs / $this->logsPerPage);
    }

    public function getPaginatedLogs($page)
    {
        $startIndex = ($page - 1) * $this->logsPerPage;
        return array_slice($this->logs, $startIndex, $this->logsPerPage);
    }

    public function getPaginationData()
    {
        return [
            'totalLogs' => $this->totalLogs,
            'totalPages' => $this->totalPages,
        ];
    }

    public function deleteSelectedLogs($selectedLogs)
    {
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $updatedLines = array_filter($lines, function ($line, $index) use ($selectedLogs) {
            return !in_array($index, $selectedLogs);
        }, ARRAY_FILTER_USE_BOTH);
        file_put_contents($this->logFile, implode(PHP_EOL, $updatedLines) . PHP_EOL);
    }

    public function deleteAllLogs()
    {
        file_put_contents($this->logFile, '');
    }

    public function getLogDetail($index)
    {
        if (!isset($this->logs[$index])) {
            return null;
        }
    
        $log = $this->logs[$index];
    
        // Obtener solo el mensaje principal (primera línea del log)
        $messageParts = explode("\n", $log['full_message']);
        $log['message'] = $messageParts[0];  // La primera línea es el mensaje principal
    
        // Desglosar el mensaje completo solo en el detalle
        $log['full_message'] = $this->parseFullMessage($log['full_message']);
    
        return $log;
    }
    
    private function parseFullMessage($fullMessage)
    {
        $parsedMessage = [
            'message' => $fullMessage,
            'ip' => null,
            'stack_trace' => null,
            'codigo' => null,
            'archivo' => null,
            'linea' => null,
            'input_data' => null,
            'user_id' => null,
            'url' => null,
        ];
    
        // Analizar el mensaje para extraer la información
        if (preg_match('/IP Address:\s*(.*)/', $fullMessage, $ipMatch)) {
            $parsedMessage['ip'] = $ipMatch[1];
        }
    
        // Actualizar la expresión regular para capturar el stack trace completo (con múltiples líneas)
        if (preg_match('/Stack Trace:\s*(.*?)(?=\nInput Data:|\nUser ID:|$)/s', $fullMessage, $stackTraceMatch)) {
            $parsedMessage['stack_trace'] = $stackTraceMatch[1];
        }
    
        if (preg_match('/Código:\s*(.*)/', $fullMessage, $codigoMatch)) {
            $parsedMessage['codigo'] = $codigoMatch[1];
        }
    
        if (preg_match('/Archivo:\s*(.*)/', $fullMessage, $archivoMatch)) {
            $parsedMessage['archivo'] = $archivoMatch[1];
        }
    
        if (preg_match('/Línea:\s*(.*)/', $fullMessage, $lineaMatch)) {
            $parsedMessage['linea'] = $lineaMatch[1];
        }

        if (preg_match('/Input Data:\s*({(?:.|\s)*?})\s*User ID:/s', $fullMessage, $inputDataMatch)) {
            $parsedMessage['input_data'] = $inputDataMatch[1];
        }                        
    
        if (preg_match('/User ID:\s*(.*)/', $fullMessage, $userIdMatch)) {
            $parsedMessage['user_id'] = $userIdMatch[1];
        }

        if (preg_match('/URL:\s*(http[s]?:\/\/[^\s]+)/', $fullMessage, $urlMatch)) {
            $parsedMessage['url'] = $urlMatch[1];
        }
    
        return $parsedMessage;
    }
    
}

?>
