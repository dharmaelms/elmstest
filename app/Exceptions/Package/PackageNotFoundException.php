<?php
namespace App\Exceptions\Package;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class PackageNotFoundException extends ApplicationException
{
    /**
     * PackageNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::PACKAGE_NOT_FOUND, $message);
    }
}