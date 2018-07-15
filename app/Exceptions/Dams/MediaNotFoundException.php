<?php

namespace App\Exceptions\Dams;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class MediaNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::MEDIA_NOT_FOUND, $message);
    }
}