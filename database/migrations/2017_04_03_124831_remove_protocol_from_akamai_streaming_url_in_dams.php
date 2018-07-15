<?php

use App\Model\Dam;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveProtocolFromAkamaiStreamingUrlInDams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $videos = Dam::where('type', 'video')->where('asset_type', 'file')->get(['id', 'transcoding', 'akamai_details']);
        $videos->each(function ($video) {
            $akamai_details = $video->akamai_details;
            if ($video->transcoding == 'yes') {
                if (isset($akamai_details['delivery_flash_url'])) {
                    $akamai_details['delivery_flash_url'] = str_replace('http:', '', $akamai_details['delivery_flash_url']);
                }
                if (isset($akamai_details['delivery_html5_url'])) {
                    $akamai_details['delivery_html5_url'] = str_replace('http:', '', $akamai_details['delivery_html5_url']);
                }
            }
            if (isset($akamai_details['stream_success_flash'])) {
                $akamai_details['stream_success_flash'] = str_replace('http:', '', $akamai_details['stream_success_flash']);
            }
            if (isset($akamai_details['stream_success_html5'])) {
                $akamai_details['stream_success_html5'] = str_replace('http:', '', $akamai_details['stream_success_html5']);
            }
            $video->akamai_details = $akamai_details;
            $video->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $videos = Dam::where('type', 'video')->where('asset_type', 'file')->get(['id', 'transcoding', 'akamai_details']);
        $videos->each(function ($video) {
            $akamai_details = $video->akamai_details;
            if ($video->transcoding == 'yes') {
                if (isset($akamai_details['delivery_flash_url'])) {
                    $akamai_details['delivery_flash_url'] = 'http:'.$akamai_details['delivery_flash_url'];
                }
                if (isset($akamai_details['delivery_html5_url'])) {
                    $akamai_details['delivery_html5_url'] = 'http:'.$akamai_details['delivery_html5_url'];
                }
            }
            if (isset($akamai_details['stream_success_flash'])) {
                $akamai_details['stream_success_flash'] = 'http:'.$akamai_details['stream_success_flash'];
            }
            if (isset($akamai_details['stream_success_html5'])) {
                $akamai_details['stream_success_html5'] = 'http:'.$akamai_details['stream_success_html5'];
            }
            $video->akamai_details = $akamai_details;
            $video->save();
        });
    }
}
