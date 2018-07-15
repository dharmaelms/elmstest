<?php

namespace App\Exceptions\Post;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class NoPostAssignedException
 * @package \App\Exceptions\Post
 */
class NoPostAssignedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::NO_POST_ASSIGNED, $message);
    }
}
