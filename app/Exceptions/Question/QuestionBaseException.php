<?php

namespace App\Exceptions\Question;

use App\Exceptions\ApplicationException;

/**
 * Class QuestionBaseException
 * @package \App\Exceptions\Question
 */
class QuestionBaseException extends ApplicationException
{
    public function __construct($code, $message = null)
    {
        parent::__construct($code, $message);
    }
}
