<?php

namespace App\Exceptions\Post;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class PostNotFoundException
 * @package \App\Exceptions\Post
 */
class PostNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::POST_NOT_FOUND, $message);
    }
}
