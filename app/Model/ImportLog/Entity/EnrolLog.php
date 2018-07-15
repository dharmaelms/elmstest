<?php namespace App\Model\ImportLog\Entity;

use Moloquent;
use App\Model\Sequence;

class EnrolLog extends Moloquent
{

    protected $collection = "erp_enrol_log";

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
    protected $dates = ['created_at'];

    protected $guarded = ["_id"];

    /**
     * function to get last inserted record id
     * @return int
     */
    public static function uniqueErpEnrolLogId()
    {
        return self::getSequence('enrol_log_id');
    }

    /** This function used to insert record
     *
     * @method InsertErpEnrolLog
     * @param  array $input
     * @return bool
     */
    public static function InsertErpEnrolLog($input)
    {
        $id = self::uniqueErpEnrolLogId();
        $log_id['rid'] = $id;
        $record = array_merge($log_id, $input);
        return self::insert($record);
    }

    /** This function used to filter on status value
     *
     * @method scopeFeedSearch
     * @param string $query
     * @param string $search
     * @return string status filter to query
     */
    public static function scopeFeedSearch($query, $search = null)
    {
        if ($search != null) {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%" . preg_replace('/\W/', '\\\\$0', $search) . "%");
            });
        }

        return $query;
    }

    /** This function used to filter on status value
     *
     * @method scopeFeedFilter
     * @param string $query
     * @param string $filter
     * @return string status filter to query
     */
    public static function scopeFeedFilter($query, $filter = 'ALL')
    {

        if ($filter != 'ALL') {
            $query->where('status', '=', $filter);
        }

        return $query;
    }

    /** This function used to filter on created date value
     *
     * @method scopeCreatedDateFilter
     * @param string $query
     * @param string $created_date
     * @return string created_date filter to query
     */
    public static function scopeCreatedDateFilter($query, $created_date)
    {

        if ($created_date != null) {
            $start = strtotime($created_date);
            $end = strtotime($created_date . ' +1 days');
            $stop = ($end - 1);
            $query->where('created_at', '>', $start)->where('created_at', '<', $stop);
        }

        return $query;
    }

    /** This function used to sort records by order
     *
     * @method scopeGetOrderBy
     * @param string $query
     * @param array $order_by
     * @return records order by created date
     */
    public static function scopeGetOrderBy($query, $order_by = ['created_at' => 'desc'])
    {
        $key = key($order_by);
        $value = $order_by[$key];
        return $query->orderBy($key, $value);
    }

    /** This function used to add pagination
     *
     * @method scopeGetByPagination
     * @param string $query
     * @param int $start
     * @param int $limit
     * @return query with pagination
     */
    public static function scopeGetByPagination($query, $start = 0, $limit = 10)
    {
        return $query->skip((int)$start)->take((int)$limit);
    }

    /** This function used to get records
     *
     * @method scopeGetAsArray
     * @param string $query
     * @return array with records as array
     */
    public static function scopeGetAsArray($query)
    {
        return $query->get()->toArray();
    }

    /** This function used to add filter on action
     *
     * @method scopeFeedAction
     * @param string $query
     * @param string $action
     * @return string with filter action
     */
    public static function scopeFeedAction($query, $action = 'ALL')
    {

        if ($action != 'ALL') {
            $query->where('action', '=', $action);
        }

        return $query;
    }

    /** This function used to get enrol log records
     *
     * @method getPackageUsergroupImportRecords
     * @param string $type
     * @param int $start
     * @param int $limit
     * @param array $orderByArray
     * @param string $search
     * @param string $created_date
     * @param string $program_sub_type
     * @param string $enrol_level
     * @return array of log records
     */
    public static function getPackageUsergroupImportRecords($type = 'ALL',
                                                            $start = 0,
                                                            $limit = 10,
                                                            $orderByArray = ['created_at' => 'desc'],
                                                            $search = null,
                                                            $created_date = null,
                                                            $program_sub_type = 'collection',
                                                            $enrol_level = 'usergroup')
    {
        return self::where('program_type', '=', 'content_feed')
            ->where('program_sub_type', '=', $program_sub_type)
            ->where('enrol_level', '=', $enrol_level)
            ->where(function ($query) {
                $query->where('type', 'exists', false);
            })
            ->FeedSearch($search)
            ->FeedFilter($type)
            ->CreatedDateFilter($created_date)
            ->GetOrderBy($orderByArray)
            ->GetByPagination($start, $limit)
            ->GetAsArray();

    }

    /** This function used to get enrol log records count
     *
     * @method getPackageUsergroupImportCount
     * @param string $type
     * @param string $search
     * @param string $created_date
     * @param string $program_sub_type
     * @param string $enrol_level
     * @param string $action
     * @return integer of log records count
     */
    public static function getPackageUsergroupImportCount($type = 'ALL',
                                                          $search = null,
                                                          $created_date = null,
                                                          $program_sub_type = 'collection',
                                                          $enrol_level = 'usergroup',
                                                          $action = 'ALL')
    {
        return self::where('program_type', '=', 'content_feed')
            ->where('program_sub_type', '=', $program_sub_type)
            ->where('enrol_level', '=', $enrol_level)
            ->where(function ($query) {
                $query->where('type', 'exists', false);
            })
            ->FeedAction($action)
            ->FeedSearch($search)
            ->FeedFilter($type)
            ->CreatedDateFilter($created_date)
            ->count();
    }

    /** This function used to get enrol log records
     *
     * @method getPackageUsergroupExportRecords
     * @param string $type
     * @param string $created_date
     * @param string $program_sub_type
     * @param string $enrol_level
     * @return array of log records
     */
    public static function getPackageUsergroupExportRecords($type = 'ALL',
                                                            $created_date = null,
                                                            $program_sub_type = 'collection',
                                                            $enrol_level = 'usergroup')
    {
        return self::where('program_type', '=', 'content_feed')
            ->where('program_sub_type', '=', $program_sub_type)
            ->where('enrol_level', '=', $enrol_level)
            ->where(function ($query) {
                $query->where('type', 'exists', false);
            })
            ->FeedFilter($type)
            ->CreatedDateFilter($created_date)
            ->GetAsArray();

    }
}
