<?php
namespace App\Enums\QuizAttempt;

use App\Enums\BaseEnum;

/**
 * Class QuizAttemptStatus
 *
 * @package App\Enums\QuizAttempt
 */
final class QuizAttemptStatus extends BaseEnum
{
    /**
     * Attempt status will be opened
     */
    const OPENED = 'OPENED';

    /**
     * Attempt status will be closed
     */
    const CLOSED = 'CLOSED';
}
