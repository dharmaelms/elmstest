<?php

namespace App\Exceptions\Quiz;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class KeywordNotFoundException
 * @package \App\Exceptions\Quiz
 */
class KeywordNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::QUESTION_KEYWORD_NOT_FOUND, $message);
    }
}
