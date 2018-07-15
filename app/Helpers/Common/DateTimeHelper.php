<?php

namespace App\Helpers\Common;

/**
 * Class DateTimeHelper
 * @package \App\Helpers\Common
 */
class DateTimeHelper
{
    const UTC_TIMEZONE = "UTC";

    public static function convertTimeStringToSeconds($time)
    {
        $temp = explode(':', trim($time));
        return ((($temp[0] * 60) + $temp[1]) * 60);
    }
}
