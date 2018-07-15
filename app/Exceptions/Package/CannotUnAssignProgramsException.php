<?php

namespace App\Exceptions\Package;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class CannotUnAssignProgramsException extends ApplicationException
{
    /**
     * CannotUnAssignProgramsException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::PACKAGE_CANNOT_UN_ASSIGN_PROGRAMS, $message);
    }
}
