<?php

namespace App\Exceptions\RolesAndPermissions;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class PermissionNotFoundException extends ApplicationException
{
    /**
     * ContextNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::PERMISSION_NOT_FOUND, $message);
    }
}
