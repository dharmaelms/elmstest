<?php

namespace App\Enums\Report;

use App\Enums\BaseEnum;

abstract class ReportPermission extends BaseEnum
{
    const VIEW_REPORT = 'view-report';
    
    const EXPORT_REPORT = 'export-report';
}
