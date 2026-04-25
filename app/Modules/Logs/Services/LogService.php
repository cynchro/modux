<?php

namespace App\Modules\Logs\Services;

// Delegates to the Admin LogService — both modules share the same log file and parsing logic.
class LogService extends \App\Modules\Admin\Services\LogService
{
}
