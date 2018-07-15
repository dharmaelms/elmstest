<?php
namespace App\Enums\Elastic;

use App\Enums\BaseEnum;

/**
 * class Types
 *
 * @package App\Enums\Elastic
 */
class Types extends BaseEnum
{
    const ASSESSMENT = 'quizzes';
    const EVENT = 'events';
    const PACKAGE = 'packages';
    const PROGRAM = 'programs';
    const POST = 'posts';
    const ITEM = 'items';
    const ASSIGNMENT = 'assignment';
}
