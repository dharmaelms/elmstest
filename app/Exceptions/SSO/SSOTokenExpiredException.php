<?php
namespace App\Exceptions\SSO;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class SSOTokenExpiredException
 * @package \App\Exceptions\SSO
 */
class SSOTokenExpiredException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::SSO_TOKEN_EXPIRED, $message);
    }
}
