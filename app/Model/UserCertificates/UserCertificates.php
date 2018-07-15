<?php

namespace App\Model\UserCertificates;

use App\Model\Sequence;
use Moloquent;

class UserCertificates extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'user_certificates';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    // protected $dates = ['created_at', 'updated_at'];

    /**
     * function used to get max id of program certificates
     * @return integer
     */
    public static function getMaxId()
    {
        return Sequence::getSequence('certificate_id');
    }

    /**
     * Scope for user filter
     * @param $query
     * @param $condition
     * @return
     */
    public function scopeFilterBy($query, $condition)
    {
        if (!$condition == '') {
            $columns_and_conditions = explode('-', $condition);
            $columns = ['name' => 'program_title', 'date' => 'updated_at'];
            return $query->orderBy($columns[$columns_and_conditions[0]], $columns_and_conditions[1]);
        }
        return $query->orderBy('updated_at', 'desc');
    }

    /**
     * Scope for title search
     */
    public function scopeSearchTitle($query, $title)
    {
        if (!$title == '') {
            $query->where('program_title', 'like', '%' . $title . '%');
        }
        return $query;
    }
}
