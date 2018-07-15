<?php
namespace App\Exceptions\Package;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class NoPackageAssignedException extends ApplicationException
{
    /**
     * NoPackageAssignedException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::NO_PACKAGE_ASSIGNED, $message);
    }
}
