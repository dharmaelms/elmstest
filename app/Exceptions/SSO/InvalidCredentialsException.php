<?php
namespace App\Exceptions\SSO;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class InvalidCredentialsException
 * @package \App\Exceptions\SSO
 */
class InvalidCredentialsException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::INVALID_CREDENTIALS, $message);
    }
}
