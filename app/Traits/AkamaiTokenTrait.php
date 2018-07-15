<?php
namespace App\Traits;

use App\Libraries\akamai\AkamaiTokenGeneration;
use Config;

/**
 * Class AkamaiTokenTrait
 * @package App\Traits
 */
trait AkamaiTokenTrait
{

    /**
     * @param $input
     * @return null|string
     */
    public function getToken($input)
    {

        /* Get The token only for Akamai Video */
        $token = null;

        if (is_array($input) && !empty($input)) {
            $duration = $this->getVideoDuration($input);
            if ('on' === Config::get('app.enable_video_token_encryption') &&
                array_key_exists('akamai_details', $input)
            ) {
                try {
                    $akamaiTokenObj = new AkamaiTokenGeneration($duration);
                    $token = "hdnea=" . $akamaiTokenObj->getToken();
                } catch (\Exception $e) {
                    //TODO: Satish: Please change this to only Log exception and handle in calling methods
                    exit($e->getMessage());
                }
            }
        }

        return $token;
    }

    /**
     * @param $input
     * @return int
     */
    public function getVideoDuration($input)
    {
        $duration = 0;

        if (
            (array_get($input, 'type') == 'video') &&
            (array_get($input, 'asset_type') == 'file') &&
            (array_key_exists('akamai_details', $input))
        ) {
            $duration = (Config::get('app.video_token_buffer_mins') * 60);
        }

        return (int)$duration;
    }
}
