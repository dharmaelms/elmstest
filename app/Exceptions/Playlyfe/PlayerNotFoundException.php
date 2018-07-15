<?php

namespace App\Exceptions\Playlyfe;

use App\Services\Common\ErrorCode;

/**
 * Class PlayerNotFoundException
 * @package \App\Exceptions\Playlyfe
 */
class PlayerNotFoundException extends PlaylyfeServiceException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::PLAYER_NOT_FOUND, $message);
    }
}
