<?php

namespace App\Exceptions\User;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class UserEntityRelationNotFoundException extends ApplicationException
{
    /**
     * UserEntityRelationNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::USER_ENTITY_RELATION_NOT_FOUND, $message);
    }
}
