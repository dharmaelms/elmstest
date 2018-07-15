<?php

namespace App\Exceptions\Module;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class ModuleNotFoundException extends ApplicationException
{
    /**
     * ModuleNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::MODULE_NOT_FOUND, $message);
    }
}