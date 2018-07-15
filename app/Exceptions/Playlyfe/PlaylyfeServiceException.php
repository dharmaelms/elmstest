<?php

namespace App\Exceptions\Playlyfe;

use App\Exceptions\ApplicationException;

/**
 * Class PlaylyfeServiceException
 * @package \App\Exceptions\Playlyfe
 */
class PlaylyfeServiceException extends ApplicationException
{
    public function __construct($code, $message = null)
    {
        parent::__construct($code, $message);
    }
}
