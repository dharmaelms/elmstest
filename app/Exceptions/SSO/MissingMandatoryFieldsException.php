<?php
namespace App\Exceptions\SSO;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class MissingMandatoryFieldsException
 * @package \App\Exceptions\SSO
 */
class MissingMandatoryFieldsException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::MISSING_MANDATORY_FIELDS, $message);
    }
}
