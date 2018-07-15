<?php
namespace App\Exceptions\RolesAndPermissions;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class RoleNotFoundException extends ApplicationException
{
    /**
     * RoleNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::ROLE_NOT_FOUND, $message);
    }
}