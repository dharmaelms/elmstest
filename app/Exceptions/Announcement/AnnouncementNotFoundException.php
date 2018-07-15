<?php

namespace App\Exceptions\Announcement;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class AnnouncementNotFoundException
 * @package \App\Exceptions\Announcement
 */
class AnnouncementNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::ANNOUNCEMENT_NOT_FOUND, $message);
    }
}
