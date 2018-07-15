<?php
namespace App\Exceptions\SSO;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class SSOTokenNotFoundException
 * @package \App\Exceptions\SSO
 */
class SSOTokenNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::SSO_TOKEN_NOT_FOUND, $message);
    }
}
