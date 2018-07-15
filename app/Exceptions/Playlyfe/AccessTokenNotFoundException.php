<?php

namespace App\Exceptions\Playlyfe;

use App\Services\Common\ErrorCode;

/**
 * Class AccessTokenNotFoundException
 * @package \App\Exceptions\Playlyfe
 */
class AccessTokenNotFoundException extends PlaylyfeServiceException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::ACCESS_TOKEN_NOT_FOUND, $message);
    }
}
