<?php

namespace App\Exceptions\Event;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class EventNotFoundException
 * @package \App\Exceptions\Event
 */
class EventNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::EVENT_NOT_FOUND, $message);
    }
}
