<?php

namespace App\Exceptions\User;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class UserNotFoundException
 * @package \App\Exceptions\User
 */
class UserNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::USER_NOT_FOUND, $message);
    }
}
