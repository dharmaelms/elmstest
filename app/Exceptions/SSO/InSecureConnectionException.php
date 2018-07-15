<?php
namespace App\Exceptions\SSO;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class InSecureConnectionException
 * @package \App\Exceptions\SSO
 */
class InSecureConnectionException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::IN_SECURE_CONNECTION, $message);
    }
}
