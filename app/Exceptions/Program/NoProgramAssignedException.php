<?php

namespace App\Exceptions\Program;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class NoProgramAssignedException
 * @package \App\Exceptions\Program
 */
class NoProgramAssignedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::NO_PROGRAM_ASSIGNED, $message);
    }
}
