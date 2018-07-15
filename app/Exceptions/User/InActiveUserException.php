<?php

namespace App\Exceptions\User;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class InActiveUserException
 * @package \App\Exceptions\User
 */
class InActiveUserException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::IN_ACTIVE_USER, $message);
    }
}
