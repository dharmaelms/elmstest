<?php

namespace App\Exceptions\Event;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class NoEventAssignedException
 * @package \App\Exceptions\Event
 */
class NoEventAssignedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::NO_EVENT_ASSIGNED, $message);
    }
}
