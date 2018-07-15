<?php
namespace App\Enums\QuizAttempt;

use App\Enums\BaseEnum;

/**
 * Class QuizAttemptDataStatus
 *
 * @package App\Enums\QuizAttempt
 */
final class QuizAttemptDataStatus extends BaseEnum
{

    const ANSWERED = 'ANSWERED';

    const COMPLETED = 'COMPLETED';

    const CORRECT = 'CORRECT';

    const INCORRECT = 'INCORRECT';

    const NOT_VIEWED = 'NOT_VIEWED';

    const STARTED = 'STARTED';
}
