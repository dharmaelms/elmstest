<?php

namespace App\Exceptions\Question;

use App\Services\Common\ErrorCode;

/**
 * Class QuestionNotFoundException
 * @package \App\Exceptions\Question
 */
class QuestionNotFoundException extends QuestionBaseException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::QUESTION_NOT_FOUND, $message);
    }
}
