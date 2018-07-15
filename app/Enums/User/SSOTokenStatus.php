<?php
namespace App\Enums\User;

use App\Enums\BaseEnum;

/**
 * class SSOTokenStatus
 * @package App\Enums\User
 */
class SSOTokenStatus extends BaseEnum
{
    /**
     * When token generated
     */
    const NOT_USED = 'NOT_USED';

    /**
     * When token used
     */
    const USED = 'USED';
}
