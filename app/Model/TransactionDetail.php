<?php

namespace App\Model;

use Moloquent;

class TransactionDetail extends Moloquent
{
    protected $collection = 'transaction_details';

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
    protected $dates = ['created_at', 'updated_at', 'start_date', 'end_date'];

    protected $casts = [
        'program_id' => 'int',
        'id' => 'int'
    ];

    public static function updateStatusByLevel($level, $id, $program_id, $data, $type = null, $package_id = null)
    {
        if ($level == 'user') {
            if ($type == 'collection') {
                return self::where('trans_level', '=', 'user')->where('id', '=', $id)->where('program_id', '=', $program_id)->where('program_sub_type', '=', $type)->where('package_id', '=', (int)$package_id)->update($data);
            } else {
                self::where('trans_level', '=', 'user')->where('id', '=', $id)->where('program_id', '=', $program_id)->Where('program_sub_type', '=', 'single')->update($data);
                return self::where('trans_level', '=', 'user')->where('id', '=', $id)->where('program_id', '=', $program_id)->where('program_sub_type', 'exists', false)->update($data);
            }
        }
        if ($level == 'usergroup') {
            if ($type == 'collection') {
                return self::where('trans_level', '=', 'usergroup')->where('id', '=', $id)->where('program_id', '=', $program_id)->where('program_sub_type', '=', $type)->where('package_id', '=', (int)$package_id)->update($data);
            } else {
                self::where('trans_level', '=', 'usergroup')->where('id', '=', $id)->where('program_id', '=', (int)$program_id)->Where('program_sub_type', '=', 'single')->update($data);
                return self::where('trans_level', '=', 'usergroup')->where('id', '=', $id)->where('program_id', '=', (int)$program_id)->where('program_sub_type', 'exists', false)->update($data);
            }
        }
    }

    //Added by Sahana
    public static function getProgramIds($id, $forfeeds = null, $type = "all")
    {
        $now = time();
        if ($type != "all") {
            $user_slugs = self::where('trans_level', '=', 'user')
                ->where('id', '=', (int)$id)
                ->where('type', '=', $type)
                ->where('duration.days', '=', 'forever')
                /*->orWhere(function($query) use($now){
                    $query->where('start_date', '<=', $now)
                          ->Where('end_date', '>=', $now);
                    })*/
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
            $group_ids = UserGroup::where('status', '!=', 'DELETED')
                ->where('relations.active_user_usergroup_rel', '=', (int)$id)
                ->lists('ugid')
                ->all();
            $usergroup_slugs = self::where('trans_level', '=', 'usergroup')
                ->whereIn('id', $group_ids)
                ->where('type', '=', $type)
                ->where('duration.days', '=', 'forever')
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
        } else {
            $user_slugs = self::where('trans_level', '=', 'user')
                ->where('id', '=', (int)$id)
                ->where('duration.days', '=', 'forever')
                /*->orWhere(function($query) use($now){
                    $query->where('start_date', '<=', $now)
                          ->Where('end_date', '>=', $now);
                    })*/
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
            $group_ids = UserGroup::where('status', '!=', 'DELETED')
                ->where('relations.active_user_usergroup_rel', '=', (int)$id)
                ->lists('ugid')
                ->all();
            $usergroup_slugs = self::where('trans_level', '=', 'usergroup')
                ->whereIn('id', $group_ids)
                ->where('duration.days', '=', 'forever')
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
        }

        $array = array_unique(array_merge($user_slugs, $usergroup_slugs));
        if ($forfeeds == null) {
            return Program::whereIn('program_slug', $array)
                ->where('program_startdate', '<=', $now)
                ->Where('program_enddate', '>=', $now)
                ->lists('program_slug')
                ->all();
        } else {
            return $array;
        }
    }

    public static function getUserActiveParent($id, $package_id)
    {
        return self::where('id', '=', $id)->
        where('package_id', '=', $package_id)->
        where('trans_level', '=', 'user')->
        count();

        /*return self::where('id', '=', $id)->
                     where('package_id', '=', $package_id)->
                     where(function($query){
                        $query->orwhere('trans_level', '=', 'user')->
                                orwhere('trans_level', '=', 'usergroup');
                     })->count();*/
    }

    public static function getUserGroupActiveParent($id, $package_id)
    {
        return self::where('package_id', '=', (int)$package_id)->
        where('trans_level', '=', 'usergroup')->
        count();
    }

    public static function getUserProgramDates($userid, $pid)
    {
        return self::where('trans_level', '=', 'user')->
        where('id', '=', $userid)->
        where('program_id', '=', $pid)->
        take(1)->
        get()->toArray();
    }

    public static function getChannelIds($id, $forfeeds = null, $type = "all", $program_sub_type = null)
    {
        $now = time();
        if ($type != "all") {
            $user_slugs = self::where('trans_level', '=', 'user')
                ->where('id', '=', (int)$id)
                ->where('type', '=', $type)
                ->where('duration.days', '=', 'forever')
                ->where('program_sub_type', '!=', $program_sub_type)
                /*->orWhere(function($query) use($now){
                    $query->where('start_date', '<=', $now)
                          ->Where('end_date', '>=', $now);
                    })*/
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
            $group_ids = UserGroup::where('status', '!=', 'DELETED')
                ->where('relations.active_user_usergroup_rel', '=', (int)$id)
                ->lists('ugid')
                ->all();
            $usergroup_slugs = self::where('trans_level', '=', 'usergroup')
                ->whereIn('id', $group_ids)
                ->where('type', '=', $type)
                ->where('duration.days', '=', 'forever')
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
        } else {
            $user_slugs = self::where('trans_level', '=', 'user')
                ->where('id', '=', (int)$id)
                ->where('duration.days', '=', 'forever')
                /*->orWhere(function($query) use($now){
                    $query->where('start_date', '<=', $now)
                          ->Where('end_date', '>=', $now);
                    })*/
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
            $group_ids = UserGroup::where('status', '!=', 'DELETED')
                ->where('relations.active_user_usergroup_rel', '=', (int)$id)
                ->lists('ugid')
                ->all();
            $usergroup_slugs = self::where('trans_level', '=', 'usergroup')
                ->whereIn('id', $group_ids)
                ->where('duration.days', '=', 'forever')
                ->where('status', '=', 'COMPLETE')
                ->lists('program_slug')
                ->all();
        }

        $array = array_unique(array_merge($user_slugs, $usergroup_slugs));
        if ($forfeeds == null) {
            return Program::whereIn('program_slug', $array)
                ->where('program_startdate', '<=', $now)
                ->Where('program_enddate', '>=', $now)
                ->lists('program_slug')
                ->all();
        } else {
            return $array;
        }
    }

    public static function getTransDetailOfProgram($programSlug, $uid)
    {
        $group_ids = UserGroup::where('status', '!=', 'DELETED')
                ->where('relations.active_user_usergroup_rel', '=', (int)$uid)
                ->lists('ugid')
                ->all();
        $query = self::where(function ($q) use ($uid) {
            $q->where('trans_level', 'user')
            ->Where('id', $uid);
        });
        if (!empty($group_ids)) {
            $query->orWhere(function ($qe) use ($group_ids) {
                $qe->where('trans_level', 'usergroup')
                ->WhereIn('id', $group_ids);
            });
        }
        return $query->whereIn('program_slug', $programSlug)
        ->orderBy('updated_at')
        ->get();
    }

    /**
     * Overrides dates mutator when field has '' as value
     * @return array
     */
    public function getDates()
    {
        $date_mutatuor = $this->dates;

        if (isset($this->attributes['start_date']) && $this->attributes['start_date'] == '') {
            $date_mutatuor = array_diff($date_mutatuor, ['start_date']);
        }
        if (isset($this->attributes['end_date']) && $this->attributes['end_date'] == '') {
            $date_mutatuor = array_diff($date_mutatuor, ['end_date']);
        }

        return $date_mutatuor;
    }
}
