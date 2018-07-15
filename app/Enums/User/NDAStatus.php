<?php
namespace App\Enums\User;

use App\Enums\BaseEnum;

/**
 * Class NDAStatus
 *
 * @package App\Enums\User
 */
final class NDAStatus extends BaseEnum
{
    /**
     * When user responded as accepted
     */
    const ACCEPTED = 'accepted';

    /**
     * When user responded as declined
     */
    const DECLINED = 'declined';

    /**
     * When user is yet to respond
     */
    const NO_RESPONSE = 'no_response';

}