<?php
namespace App\Enums\Quiz;

use App\Enums\BaseEnum;

/**
 * Class QuizType
 *
 * @package App\Enums\Quiz
 */
final class QuizType extends BaseEnum
{
    /**
     * Quiz type will be general
     */
    const GENERAL_QUIZ = 'GENERAL';

    /**
     * Quiz type will be practice
     */
    const PRACTICE_QUIZ = 'PRACTICE_QUIZ';

    /**
     * Quiz type will be practice
     */
    const QUESTION_GENERATOR = 'QUESTION_GENERATOR';
}
