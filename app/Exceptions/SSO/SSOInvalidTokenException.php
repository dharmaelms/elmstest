<?php
namespace App\Exceptions\SSO;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class SSOInvalidTokenException
 * @package \App\Exceptions\SSO
 */
class SSOInvalidTokenException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::SSO_INVALID_TOKEN, $message);
    }
}
