<?php
namespace App\Enums\Post;

use App\Enums\BaseEnum;

/**
 * Class PostChannels
 *
 * @package App\Enums\Post
 */
final class PostChannels extends BaseEnum
{
    /**
     * When channel question contains more than 250 characters
     */
    const MAX_ALLOWED_TEXT = '250';
   
}