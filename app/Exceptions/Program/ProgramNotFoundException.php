<?php

namespace App\Exceptions\Program;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class ProgramNotFoundException
 * @package \App\Exceptions\Program
 */
class ProgramNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::PROGRAM_NOT_FOUND, $message);
    }
}
