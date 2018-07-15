<?php

namespace App\Exceptions\Authentication;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class AccessDeniedException
 * @package \App\Exceptions\Authentication
 */
class AccessDeniedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::ACCESS_DENIED, $message);
    }
}
