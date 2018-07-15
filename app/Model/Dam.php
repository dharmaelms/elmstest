<?php

namespace App\Model;

use Akamai;
use Auth;
use Config;
use DB;
use URL;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use KalturaCaptionAsset;
use KalturaCaptionClientPlugin;
use KalturaCaptionType;
use KalturaClient;
use KalturaConfiguration;
use KalturaEntryStatus;
use KalturaLanguage;
use KalturaMediaEntry;
use KalturaMediaEntryFilter;
use KalturaMediaType;
use KalturaNullableBoolean;
use KalturaSessionType;
use KalturaStringResource;
use Moloquent;

class Dam extends Moloquent
{
    protected $collection = 'dams';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    
   /**
    * Mutator to return if the url is youtube or not.
    * @return Boolean|Array
    */
    public function getIsYoutubeAttribute()
    {
        $is_youtube = false;
        if ($this->url) {
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $this->url, $match);
            $is_youtube = ((empty($match)) ? false : $match);
        }
        return $is_youtube;
    }
    /**
     * Mutator to get only youtube embed url
     * @return String|Null
     */
    public function getYoutubeEmbedCodeAttribute()
    {
        $embedable_html = null;
        $youtube = $this->is_youtube;
        if (isset($youtube[1])) {
            $embedable_html = "<iframe src=\"https://www.youtube.com/embed/{$youtube[1]}?rel=0&autohide=2&iv_load_policy=3&modestbranding=1&theme=light". $this->convertStringToSeconds($this->url, '&start=') ."\" frameborder=\"0\"/ allowfullscreen=\"true\" width=\"69%\" height=\"212px\" class=\"question-media\" data-media-id=\"{$this->_id}\"></iframe>";
        }

        return $embedable_html;
    }

    /**
     * Set attribute to check video link is ted or not
     */
    public function getIsTedAttribute()
    {
        $is_ted = false;
        if ($this->url) {
            preg_match("/ted\\.com\\/talks\\/(.+)/i", $this->url, $match);
            $is_ted = ((empty($match)) ? false : $match);
        }
        return $is_ted;
    }

    /**
     * Set attribute to generate TED embed URL
     */
    public function getTedEmbedCodeAttribute()
    {
        $embedable_html = false;
        if (isset($this->is_ted[1])) {
            $embedable_html = '<iframe src="//embed.ted.com/talks/' . $this->is_ted[1] . '"
                                    frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen 
                                    allowFullScreen>
                                </iframe>';
        }
        return $embedable_html;
    }

    /**
     * Set attribute to check video link is Vimeo or not
     */
    public function getIsVimeoAttribute()
    {
        $is_vimeo = false;
        if ($this->url) {
            preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/", $this->url, $match);
            $is_vimeo = ((empty($match)) ? false : $match);
        }
        return $is_vimeo;
    }

    /**
     * Set attribute to generate Vimeo embed URL
     */
    public function getVimeoEmbedCodeAttribute()
    {
        $embedable_html = false;
        if (isset($this->is_vimeo[5])) {
            $embedable_html = '<iframe src="//player.vimeo.com/video/' . $this->is_vimeo[5] . '"
                                    frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen 
                                    allowFullScreen>
                                </iframe>';
        }
        return $embedable_html;
    }

    /**
     * Mutator to get embed url
     * @return String
     */
    public function getEmbedCodeAttribute()
    {
        if ($this->asset_type === "file") {
            return "<iframe allowfullscreen src=\"" . URL::route("media", ["id" => $this->_id]) . "\" frameborder=\"0\" class=\"question-media\" data-media-id=\"{$this->_id}\"></iframe>";
        } else {
            $youtube_embed_code = $this->youtube_embed_code;
            if ($youtube_embed_code) {
                return $youtube_embed_code;
            } elseif ($this->is_ted) {
                return $this->ted_embed_code;
            } elseif ($this->is_vimeo) {
                return $this->vimeo_embed_code;
            } else {
                return "<a href=\"{$this->url}\" target=\"_blank\" class=\"question-media\" data-media-id=\"{$this->_id}\">{$this->name}</a>";
            }
        }
    }

    /**
     * Method to calculate youtube starting time.
     * @return Integer|String
     */
    private function convertStringToSeconds($url, $append = null)
    {
        // Parsing the url to get the query parameters
        parse_str(array_get(parse_url($url), 'query', ''), $result);

        $time = array_get($result, 't', 0);

        $start_time = 0;
        $time_values = [
            's' => 1,
            'm' => 1 * 60,
            'h' => 1 * 60 * 60,
            'd' => 1 * 60 * 60 * 24,
            'w' => 1 * 60 * 60 * 24 * 7
        ];
        $time_pairs;
        //is the format 1h30m20s etc
        if (preg_match('/^(\d+[smhdw]?)+$/', $time)) {
            $time_string = trim(preg_replace('/([smhdw])/', ' \1 ', $time));

            $time_pairs = explode(' ', $time_string);

            for ($i = 0; $i < count($time_pairs); $i += 2) {
                
                $start_time += $time_pairs[$i] * $time_values[((isset($time_pairs[$i + 1])) ? $time_pairs[$i + 1] : 's')];
            }
        }

        // $append is used in situations where the query itself has to be prepared by this method. ie) &start=10
        return ((!is_null($append)) ? $append . $start_time : $start_time);
    }

    public static function getMediaById($id)
    {
        try {
            return self::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new \Exception;
        }
    }

    public static function uniqueDAMSId()
    {
       return Sequence::getSequence('dams_id');
    }

    public static function getDAMSAsset($assetslug)
    {
        return self::where('unique_name', '=', (string)$assetslug)->get();
    }

    public static function getDAMSAssetByCreatedAt($time)
    {
        return self::where('created_at', '=', $time)->get();
    }

    public static function getDAMSAssetsWithType($type = 'all')
    {
        if ($type == 'all') {
            return self::where('_id', '!=', 'id')->get()->toArray();
        } else {
            return self::where('type', '=', $type)->get()->toArray();
        }
    }

    public static function getDAMSAssetsWithTypeWithPagination(
        $type = 'all',
        $start = 0,
        $limit = 10,
        $orderby = ['created_at' => 'desc'],
        $search = null,
        $filter_params = []
    ) {
        return self::where('_id', '!=', 'id')
            ->DAMSSearch($search)
            ->filter($filter_params)
            ->DAMSFilter($type)
            ->GetOrderBy($orderby)
            ->GetByPagination($start, $limit)
            ->get()
            ->toArray();
    }

    public static function getDamsCount(
        $type = 'all',
        $search = null,
        $filter_params = []
    ) {
        return self::where('_id', '!=', 'id')
            ->DAMSSearch($search)
            ->filter($filter_params)
            ->DAMSFilter($type)
            ->count();
    }

    /**
     * @param $query \Illuminate\Database\Query\Builder
     * @param array $filter_params
     * @return object \Illuminate\Database\Query\Builder
     */
    public function scopeFilter($query, $filter_params = [])
    {
        return $query->when(
            array_has($filter_params, "in_ids"),
            function ($query) use ($filter_params) {
                return $query->whereIn("id", $filter_params["in_ids"]);
            }
        )->when(
            array_has($filter_params, "created_by"),
            function ($query) use ($filter_params) {
                return $query->createdBy($filter_params["created_by"]);
            }
        );
    }

    /* Scopes for querying starts here */

    public static function scopeDAMSFilter($query, $filter = 'all')
    {
        if (is_array($filter)) {
            $query->whereIn("type", $filter);
        } else {
            if ($filter == 'media') {
                $query->where(function ($q) use ($filter) {
                    $q->where('type', '=', 'image'); // Removed video from the list. REMOVED CODE:"->OrWhere('type','=','video')"
                })->where('asset_type', '=', 'file');
            } elseif ($filter != 'all') {
                $query->where('type', '=', $filter);
            }
        }

        return $query;
    }

    public static function scopeDAMSSearch($query, $search = null)
    {
        if ($search != null) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhere('short_description', 'like', '%' . $search . '%')
                ->orWhere('tags', 'like', '%' . $search . '%');
        }

        return $query;
    }

    public static function scopeUserRelation($query, $userid = null, $usergrpids = [])
    {
        if ($userid) {
            $query->where(function ($q) use ($userid, $usergrpids) {
                $q->whereIn('relations.active_usergroup_media_rel', $usergrpids)
                    ->orWhere('relations.active_user_media_rel', '=', $userid);
            });
        }

        return $query;
    }

    public static function scopeGetOrderBy($query, $orderby = ['created_at' => 'desc'])
    {
        $key = key($orderby);
        $value = $orderby[$key];

        return $query->orderBy($key, $value);
    }

    public static function scopeGetByPagination($query, $start = 0, $limit = 10)
    {
        return $query->skip((int)$start)->take((int)$limit);
    }

    /* Scopes for querying ends here */

    public static function getDAMSVideoAssetsUsingStatus($status = 'all')
    {
        if ($status == 'all') {
            return self::where('type', '=', 'video')->get()->toArray();
        } else {
            return self::where('type', '=', 'video')->where('video_status', '=', strtoupper($status))->get()->toArray();
        }
    }

    public static function getDAMSAssetsUsingID($id = 'all')
    {
        if ($id == 'all') {
            return self::where('_id', '!=', 'id')->get()->toArray();
        } else {
            return self::where('_id', '=', $id)->get()->toArray();
        }
    }

    public static function getmongoid($id = 'all')
    {
        //dd($id);
        if ($id == 'all') {
            return self::where('_id', '!=', 'id')->get()->toArray();
        } else {
            return self::where('id', '=', (int)$id)->value('_id');
        }
    }

    public static function getDAMSAssetsUsingAutoID($id = 'all')
    {

        if ($id == 'all') {
            return self::where('_id', '!=', 'id')->get()->toArray();
        } else {
            return self::where('id', '=', (int)$id)->get()->toArray();
        }
    }

    public static function deleteAsset($id)
    {
        self::where('_id', '=', $id)->delete();
    }

    public static function uploadToAkamai($fileinfo)
    {
        $akamai = new Akamai();
        if (isset($fileinfo['transcoding']) && $fileinfo['transcoding'] == 'yes') {
            $url = config('app.akamai.ftp_base_loc') . '/' . $fileinfo['unique_name_with_extension'];
            $response = $akamai->Push($url, base_path() . '/' . str_replace('../', '', $fileinfo['temp_location']));
            $response['stream_success_flash'] = str_replace('<url>', config('app.akamai.ftp_success_url') . '/' . $fileinfo['unique_name_with_extension'], config('app.akamai.streaming_url_flash'));
            $response['stream_success_html5'] = str_replace('<url>', config('app.akamai.ftp_success_url') . '/' . $fileinfo['unique_name_with_extension'], config('app.akamai.streaming_url_html'));
        } else {
            $url = config('app.akamai.ftp_no_transcoding_loc') . '/' . $fileinfo['unique_name_with_extension'];
            $response = $akamai->Push($url, base_path() . '/' . str_replace('../', '', $fileinfo['temp_location']));
            $response['stream_success_flash'] = str_replace('<url>', config('app.akamai.ftp_no_transcoding_url') . '/' . $fileinfo['unique_name_with_extension'], config('app.akamai.streaming_url_flash'));
            $response['stream_success_html5'] = str_replace('<url>', config('app.akamai.ftp_no_transcoding_url') . '/' . $fileinfo['unique_name_with_extension'], config('app.akamai.streaming_url_html'));
        }

        return $response;
    }

    public static function uploadToKaltura($fileinfo)
    {
        $client = self::kalturaAdminSession();
        $token = $client->baseEntry->upload($fileinfo['temp_location']);
        $entry = new KalturaMediaEntry();
        $entry->name = $fileinfo['name'];
        $entry->mediaType = KalturaMediaType::VIDEO;
        $newEntry = $client->media->addFromUploadedFile($entry, $token);
        if (isset($fileinfo['srt_location']) && !empty($fileinfo['srt_location']) && isset($newEntry->id) && file_exists($fileinfo['srt_location'])) {
            $entryId = $newEntry->id;
            $captionAsset = new KalturaCaptionAsset();
            $captionAsset->fileExt = 'srt';
            $captionAsset->language = KalturaLanguage::EN;
            $captionAsset->isDefault = KalturaNullableBoolean::TRUE_VALUE;
            $captionAsset->format = KalturaCaptionType::SRT;
            $captionPlugin = KalturaCaptionClientPlugin::get($client);
            $result = $captionPlugin->captionAsset->add($entryId, $captionAsset);
            $newEntry->srt_data = $result;
            $contentResource = new KalturaStringResource(); // Using KalturaStringResource since other Resource Handlers are not working as expected.
            $contentResource->content = file_get_contents($fileinfo['srt_location']);
            $captionPlugin = KalturaCaptionClientPlugin::get($client);
            $captionPlugin->captionAsset->setContent($result->id, $contentResource);
            self::where('srt_location', '=', $fileinfo['srt_location'])->update(['srt_status' => 'READY']);
        }

        return $newEntry;
    }

    public static function addCaptionAsset($videoid, $fileloc)
    {
        $client = self::kalturaAdminSession();
        $entryId = $videoid;
        $captionAsset = new KalturaCaptionAsset();
        $captionAsset->fileExt = 'srt';
        $captionAsset->language = KalturaLanguage::EN;
        $captionAsset->isDefault = KalturaNullableBoolean::TRUE_VALUE;
        $captionAsset->format = KalturaCaptionType::SRT;
        $captionPlugin = KalturaCaptionClientPlugin::get($client);
        $result = $captionPlugin->captionAsset->add($entryId, $captionAsset);
        $contentResource = new KalturaStringResource(); // Using KalturaStringResource since other Resource Handlers are not working as expected.
        $contentResource->content = file_get_contents($fileloc);
        $captionPlugin = KalturaCaptionClientPlugin::get($client);
        $captionPlugin->captionAsset->setContent($result->id, $contentResource);
        self::where('srt_location', '=', $fileloc)->update(['srt_status' => 'READY']);

        return $result;
    }

    public static function deleteKalturaVideo($entryID)
    {
        $client = self::kalturaAdminSession();
        $result = $client->baseEntry->delete($entryID);

        return $result;
    }

    public static function deleteAkamaiVideo($asset)
    {
        $akamai = new Akamai();
        $config = config('app.akamai');
        if (isset($asset['transcoding']) && $asset['transcoding'] == 'no') {
            $url = $config['ftp_no_transcoding_loc'] . '/' . $asset['unique_name_with_extension'];
            $akamai->delete($url);
        } elseif (isset($asset['transcoding']) && $asset['transcoding'] == 'yes') {
            // Search and Delete from uploaded folder is SKIPPED since the transcoding is on. file will always be moved to either succes folder or failure folder

            // Search and Delete from Success folder
            if (isset($asset['akamai_details']['response']['errorCode']) && $asset['akamai_details']['response']['errorCode'] == '0') {
                $url = $config['ftp_success_loc'] . '/' . $asset['unique_name_with_extension'];
                $akamai->delete($url);
            }

            // Search and Delete from failure folder
            if (isset($asset['akamai_details']['response']['errorCode']) && $asset['akamai_details']['response']['errorCode'] != '0') {
                $url = $config['ftp_failure_loc'] . '/' . $asset['unique_name_with_extension'];
                $akamai->delete($url);
            }

            // Search and Delete from Image folder
            if (isset($asset['akamai_details']['response']['deliveryBaseURL']) && isset($config['ftp_image_loc']) && $config['ftp_image_loc'] && isset($config['video_thumbnail']) && $config['video_thumbnail'] == 'enabled') {
                $url = $config['ftp_image_loc'] . '/' . substr($asset['akamai_details']['response']['deliveryBaseURL'], 0, strrpos($asset['akamai_details']['response']['deliveryBaseURL'], '/'));
                $response = $akamai->dir($url);
                if ($akamai->statusCode() != 404) {
                    $xml = simplexml_load_string($response);
                    foreach ($xml->children() as $node) {
                        if ($node['type'] == 'file') {
                            $newurl = $url . '/' . $node['name'];
                            $akamai->delete($newurl);
                        }
                    }
                }
                $akamai->rmdir($url);
                $url = substr($url, 0, strrpos($url, '/'));
                $akamai->rmdir($url);
                $url = substr($url, 0, strrpos($url, '/'));
                $akamai->rmdir($url);
            }

            // Search and Delete from Delivery folder
            if (isset($asset['akamai_details']['response']['deliveryBaseURL']) && isset($config['ftp_delivery_loc']) && $config['ftp_delivery_loc']) {
                $url = $config['ftp_delivery_loc'] . '/' . substr($asset['akamai_details']['response']['deliveryBaseURL'], 0, strrpos($asset['akamai_details']['response']['deliveryBaseURL'], '/'));
                $response = $akamai->dir($url);
                if ($akamai->statusCode() != 404) {
                    $xml = simplexml_load_string($response);
                    foreach ($xml->children() as $node) {
                        if ($node['type'] == 'file') {
                            $newurl = $url . '/' . $node['name'];
                            $akamai->delete($newurl);
                        }
                    }
                }
                $akamai->rmdir($url);
                $url = substr($url, 0, strrpos($url, '/'));
                $akamai->rmdir($url);
                $url = substr($url, 0, strrpos($url, '/'));
                $akamai->rmdir($url);
            }

            // Delete the local thumbnail copy
            if (file_exists(config('app.dams_video_thumb_path') . $asset['unique_name'] . '.png')) {
                unlink(config('app.dams_video_thumb_path') . $asset['unique_name'] . '.png');
            }
        }
    }

    public static function deleteCaptionAsset($captionAssetID)
    {
        $client = self::kalturaAdminSession();
        $captionPlugin = KalturaCaptionClientPlugin::get($client);
        $result = $captionPlugin->captionAsset->delete($captionAssetID);

        return $result;
    }

    public static function getKalturaFilesWithREADYStatus()
    {
        $client = self::kalturaAdminSession();
        $filter = new KalturaMediaEntryFilter();
        $filter->statusEqual = KalturaEntryStatus::READY;
        $kaltura_record = $client->baseEntry->listAction($filter);

        return $kaltura_record->objects;
    }

    public static function kalturaConfig()
    {
        return ['partner_id' => Config::get('app.partnerId'), 'admin_secret' => Config::get('app.admin_secret')];
    }

    public static function kalturaAdminSession()
    {
        $kalturaConfig = self::kalturaConfig();
        $partnerUserID = '';
        $config = new KalturaConfiguration($kalturaConfig['partner_id']);
        $client = new KalturaClient($config);
        $ks = $client->session->start($kalturaConfig['admin_secret'], $partnerUserID, KalturaSessionType::ADMIN);
        $client->setKs($ks);

        return $client;
    }

    public static function logInfo($data)
    {
        return DB::collection('damsbulkimport')->insert($data);
    }

    public static function getDAMSBulkImportRecords($status)
    {
        return DB::collection('damsbulkimport')->where('status', '=', strtoupper($status))->get();
    }

    public static function getDAMSBulkImport($assetslug)
    {
        return DB::collection('damsbulkimport')->where('unique_name', '=', (string)$assetslug)->get();
    }

    public static function getDAMSBulkImportStatusUpdate($assetslug)
    {
        $query = ['$set' => ['status' => 'IMPORTED']];

        return DB::collection('damsbulkimport')->where('unique_name', '=', (string)$assetslug)->update($query);
    }

    public static function updateDAMSBulkImportExcelData($assetslug, $data)
    {
        $query = ['$set' => ['exceldata' => $data]];

        return DB::collection('damsbulkimport')->where('unique_name', '=', (string)$assetslug)->update($query);
    }

    public static function getDAMSBulkImportInsert($insertArr)
    {
        return DB::collection('damsbulkimport')->insert($insertArr);
    }

    // public static function getDAMSRelation($media_id){
    //  return DB::collection('damsrelation')->where('media_id', $media_id)->get();
    // }

    // public static function insertDAMSRelation($insertArr){
    //  return DB::collection('damsrelation')->insert($insertArr);
    // }

    public static function removeMediaRelation($key, $fieldarr = [], $id = null)
    {
        if ($id) {
            foreach ($fieldarr as $field) {
                self::where('_id', $key)->pull('relations.' . $field, $id);
            }
        }

        return self::where('_id', $key)->update(['updated_at' => time()]);
    }

    public static function updateDAMSRelation($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('_id', $key)->unset('relations.' . $arrname);
            self::where('_id', $key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            self::where('_id', $key)->push('relations.' . $arrname, $updateArr, true);
        }

        return self::where('_id', $key)->update(['updated_at' => time()]);
    }

    public static function removeMediaRelationUsingID($key, $fieldarr = [], $id = null)
    {
        foreach ($fieldarr as $field) {
            self::where('id', $key)->pull('relations.' . $field, $id);
        }

        return self::where('id', $key)->update(['updated_at' => time()]);
    }

    public static function updateDAMSRelationUsingID($key, $arrname, $updateArr, $overwrite = false)
    {
        if ($overwrite) {
            self::where('id', $key)->unset('relations.' . $arrname);
            self::where('id', $key)->update(['relations.' . $arrname => $updateArr]);
        } else {
            self::where('id', $key)->push('relations.' . $arrname, $updateArr, true);
        }

        return self::where('id', $key)->update(['updated_at' => time()]);
    }

    //added by sahana
    public static function removeMediaRelationId($key, $fieldarr = [], $id = null)
    {
        if ($id) {
            foreach ($fieldarr as $field) {
                self::where('id', $key)->pull('relations.' . $field, (int)$id);
            }
        }

        return self::where('id', $key)->update(['updated_at' => time()]);
    }

    public static function addMediaRelation($key, $fieldarr = [], $id = null)
    {
        if ($id) {
            foreach ($fieldarr as $field) {
                self::where('id', $key)->push('relations.' . $field, (int)$id, true);
            }
        }

        return self::where('id', $key)->update(['updated_at' => time()]);
    }

    /*for statistic */
    public static function getLastThirtydaysCreatedMediaCount()
    {
        return self::where('status', '=', 'ACTIVE')->where('created_at', '>', strtotime('-30 day', time()))->get()->count();
    }

    public static function getDAMSMediaUsingID($id = 'all')
    {
        if ($id == 'all') {
            return self::where('_id', '!=', 'id')->get()->toArray();
        } else {
            return self::where('id', '=', (int)$id)->get()->toArray();
        }
    }

    public static function saveResponse($response)
    {
        DB::collection('response')->insert($response);

        return true;
    }

    public static function getAssetType($id, $pluckdata = 'type')
    {
        return self::where('id', '=', $id)->value($pluckdata);
    }

    /*for reports*/
    public static function getLastDayLibraryItems()
    {

        /*$start_time = strtotime('yesterday midnight');
        $end_time = $start_time + 86400;

        return self::whereBetween('date', [$start_time, $end_time])
                    ->get(['id', 'name', 'asset_type', 'liked_count', 'users_liked'])
                    ->toArray();*/
        return self::get(['id', 'name', 'asset_type', 'liked_count', 'users_liked'])
            ->toArray();
    }

    public static function getMediaCollection($mediaCollection)
    {
        return self::whereIn("_id", $mediaCollection)->get();
    }

    public static function getMediaNameByID($ids)
    {
        return self::whereIn('id', $ids)->get(['id', 'name', 'created_by_username', 'created_at', 'type']);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|array $username
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeCreatedBy($query, $username)
    {
        if (is_array($username)) {
            return $query->whereIn("created_by_username", $username);
        } else {
            return $query->where("created_by_username", $username);
        }
    }
}
