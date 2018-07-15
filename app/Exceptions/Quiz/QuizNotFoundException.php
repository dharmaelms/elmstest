<?php

namespace App\Exceptions\Quiz;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

/**
 * Class QuizNotFoundException
 * @package \App\Exceptions\Quiz
 */
class QuizNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::QUIZ_NOT_FOUND, $message);
    }
}
