<?php
namespace App\Exceptions\SSO;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class InvalidRequestException
 * @package \App\Exceptions\SSO
 */
class InvalidRequestException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::INVALID_REQUEST, $message);
    }
}
