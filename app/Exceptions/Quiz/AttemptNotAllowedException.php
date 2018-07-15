<?php

namespace App\Exceptions\Quiz;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class AttemptNotAllowedException
 * @package \App\Exceptions\Quiz
 */
class AttemptNotAllowedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::ATTEMPT_NOT_ALLOWED, $message);
    }
}
