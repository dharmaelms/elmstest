<?php

namespace App\Exceptions\User;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class RelationNotFoundException
 * @package \App\Exceptions\User
 */
class RelationNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::RELATION_NOT_FOUND, $message);
    }
}
