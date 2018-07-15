<?php

namespace App\Exceptions;

use Exception;

/**
 * Class ApplicationException
 * @package \App\Exceptions
 */
class ApplicationException extends Exception
{
    public function __construct($code, $message = null)
    {
        if (is_null($message)) {
            $message = trans("admin/exception.{$code}");
        }
        parent::__construct($message, $code);
    }
}
