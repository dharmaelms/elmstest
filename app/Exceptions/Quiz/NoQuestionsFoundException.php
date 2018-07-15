<?php

namespace App\Exceptions\Quiz;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class NoQuestionsFoundException
 * @package \App\Exceptions\Quiz
 */
class NoQuestionsFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::NO_QUESTIONS_FOUND, $message);
    }
}
