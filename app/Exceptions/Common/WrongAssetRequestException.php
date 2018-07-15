<?php

namespace App\Exceptions\Common;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class WrongAssetRequestException
 * @package \App\Exceptions\Common
 */
class WrongAssetRequestException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::ASSET_REQUEST_ERROR, $message);
    }
}
