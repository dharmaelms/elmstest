<?php

namespace App\Exceptions\UserGroup;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class UserNotFoundException
 * @package \App\Exceptions\User
 */
class UserGroupNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::USER_GROUP_NOT_FOUND, $message);
    }
}
