<?php

namespace App\Exceptions\Post;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class QuestionNotFoundException extends ApplicationException
{
    /**
     * QuestionNotFoundException constructor.
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::POST_QUESTION_NOT_FOUND, $message);
    }
}
