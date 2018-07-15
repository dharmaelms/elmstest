<?php
namespace App\Enums\Quiz;

use App\Enums\BaseEnum;

/**
 * Class CutoffFormatType
 *
 * @package App\Enums\Quiz
 */
final class CutoffFormatType extends BaseEnum
{
    /**
     * Cutoff format will be marks
     */
    const MARK = 'MARK';

    /**
     * Cutoff format will be percentage
     */
    const PERCENTAGE = 'PERCENTAGE';
}
