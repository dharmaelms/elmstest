<?php

namespace App\Exceptions\RolesAndPermissions;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class UserRoleMappingNotFoundException extends ApplicationException
{
    /**
     * UserRoleMappingNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::USER_ROLE_MAPPING_NOT_FOUND, $message);
    }
}