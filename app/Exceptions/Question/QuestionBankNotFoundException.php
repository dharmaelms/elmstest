<?php

namespace App\Exceptions\Question;

use App\Services\Common\ErrorCode;

/**
 * Class QuestionBankNotFoundException
 * @package \App\Exceptions\Question
 */
class QuestionBankNotFoundException extends QuestionBaseException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::QUESTION_BANK_NOT_FOUND, $message);
    }
}
