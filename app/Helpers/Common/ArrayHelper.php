<?php

namespace App\Helpers\Common;

/**
 * Class ArrayHelper
 * @package \App\Helpers\Common
 */
class ArrayHelper
{
    /**
     * If the given value is not an array, wrap it in one.
     * Note: This helper method is added in Laravel 5.4. Since, we
     *      are in Laravel 5.2 it will be helpful.
     *
     * @param mixed $value
     * @return array
     */
    public static function wrap($value)
    {
        return ! is_array($value) ? [$value] : $value;
    }
}
