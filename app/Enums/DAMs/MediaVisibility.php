<?php
namespace App\Enums\DAMs;

use App\Enums\BaseEnum;

/**
 * Class MediaVisibility
 *
 * @package App\Enums\BaseEnum
 */
final class MediaVisibility extends BaseEnum
{
    /**
     * Public media
     */
    const PUBLIC_MEDIA = 'public';

    /**
     * Private media
     */
    const PRIVATE_MEDIA = 'private';
}
