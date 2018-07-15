<?php

namespace App\Exceptions\Common;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class AssetNotFoundException
 * @package \App\Exceptions\Common
 */
class AssetNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::ASSET_NOT_FOUND, $message);
    }
}
