<?php

namespace App\Model;

use Moloquent;

class DimensionChannel extends Moloquent
{
    protected $collection = 'dim_channels';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['create_date', 'update_date'];

    public function getTitleAttribute()
    {
        return html_entity_decode($this->channel_name);
    }

    public function getSubNameAttribute()
    {
        return html_entity_decode($this->short_name);
    }

    public static function isExist($channel_id = 0)
    {
        return self::where('channel_id', '=', (int)$channel_id)->first();
    }

    public static function getChannelsDetails($channel_ids = [])
    {
        if (!empty($channel_ids)) {
            return self::whereIn('channel_id', $channel_ids)
                ->orderBy('channel_name', 'asc')
                ->get(['post_count', 'post_ids', 'channel_id', 'items', 'channel_slug', 'channel_name'])
                ->toArray();
        } else {
            return [];
        }
    }

    public static function getChannelSlugsAndIds()
    {
        return self::get(['channel_slug', 'channel_id', 'user_count', 'post_count', 'items'])
            ->toArray();
    }

    public static function getChannelSlugsNameAndIds()
    {
        return self::get(['channel_slug', 'channel_id', 'channel_name', 'short_name'])
            ->toArray();
    }

    public static function getChannelDetails($channel_ids = [])
    {
        return self::whereIn('channel_id', $channel_ids)
            ->get();
    }
}
