<?php namespace App\Model\ImportLog\Entity;

use App\Enums\Cron\CronBulkImport;
use Auth;
use Moloquent;
use App\Model\Sequence;

class UsergroupLog extends Moloquent
{
    
    protected $collection = "erp_usergroup_log";

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
    public static function uniqueErpUsergroupLogId()
    {
        return Sequence::getSequence('usergroup_log_id');
    }

    /** This function used to insert usergroup log
     *
     * @method getInsertErpUserGroupLog
     * @param array $input
     */
    public static function insertErpUserGroupLog($input)
    {
        $log_id['rid'] = self::uniqueErpUsergroupLogId();
        $record = array_merge($log_id, $input);
        self::insert($record);
    }

    /** This function used to prepare usergroup insert data
     *
     * @method prepareUgLogData
     * @param  array $data
     * @return array $input
     */
    public static function prepareUgLogData($data)
    {
        $input['usergroup_name'] = trim($data['usergroup']);
        $input['ug_name_lower'] = trim(strtolower($data['usergroup']));
        $input['usergroup_email'] = '';
        $input['description'] = '';
        $input['status'] = 'ACTIVE';
        return $input;
    }

    /** This function used to prepare usergroup log data
     *
     * @method getUgLogData
     * @param int $group_id
     * @return array
     */
    public static function getUgLogData($group_id,$cron)
    {
        $group_log['groupid'] = $group_id;
        $group_log['created_by'] = ($cron == 0) ? Auth::user()->username : CronBulkImport::CRONUSER;
        $group_log['created_at'] = time();
        $group_log['status'] = 'SUCCESS';
        $group_log['action'] = 'ADD';
        return $group_log;
    }
    /** This function used to get user to usergroup mapping count
     *
     * @method getUserGroupUpdateCount
     * @param string $type
     * @param string $search
     * @param string $created_date
     * @param string $action
     * @return integer of user to usergroup mapping records
     */
    public static function getUserGroupUpdateCount($type = 'ALL', $search = null, $created_date = null, $action = 'ALL')
    {
        return self::where(function ($query) {
            $query->where('type', 'exists', false);
        })
            ->FeedAction($action)
            ->FeedSearch($search)
            ->FeedFilter($type)
            ->CreatedDateFilter($created_date)
            ->count();
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
    /** This function used to add filter on operation
     *
     * @method scopeFeedOperation
     * @param string $query
     * @param string $action
     * @return string with filter operation
     */
    public static function scopeFeedOperation($query, $action = 'all')
    {

        if ($action != 'all') {
            $query->where('operation', '=', $action);
        }

        return $query;
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
    /** This function used to get user-user group import count
     *
     * @method getUserImportCount
     * @param string $type
     * @param string $search
     * @param string $created_date
     * @param string $action
     * @return integer of user log records
     */
    public static function getUserUgImportCount($type = 'ALL', $search = null, $created_date = null, $action = 'all')
    {
        return self::where(function ($query) {
            $query->where('type', 'exists', false)->where('action', '=', 'UPDATE');
        })
        ->FeedOperation($action)
        ->FeedSearch($search)
        ->FeedFilter($type)
        ->CreatedDateFilter($created_date)
        ->count();
    }

    /** This function used to get user-user group log records
     *
     * @method  getUserUgImportRecords
     * @param string $type
     * @param int $start
     * @param int $limit
     * @param array $orderByArray
     * @param string $search
     * @param string $created_date
     * @param string $action
     * @return array of log records
     */
    public static function getUserUgImportRecords(
        $type = 'ALL',
        $start = 0,
        $limit = 10,
        $orderByArray = ['created_at' => 'desc'],
        $search = null,
        $created_date = null,
        $action = 'all'
    ) {
        return self::where(function ($query) {
            $query->where('type', 'exists', false)->where('action', '=', 'UPDATE');
        })
        ->FeedSearch($search)
        ->FeedFilter($type)
        ->CreatedDateFilter($created_date)
        ->FeedOperation($action)
        ->GetOrderBy($orderByArray)
        ->GetByPagination($start, $limit)
        ->GetAsArray();
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
     * @return string with pagination
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
     /** This function used to get user-user group log records
     *
     * @method getUserUgExportRecords
     * @param string $type
     * @param string $created_date
     * @param string $action
     * @return array of log records
     */
    public static function getUserUgExportRecords($type,$created_date,$action)
    {
        return self::where(function ($query) {
            $query->where('type', 'exists', false)->where('action','=','UPDATE');
        })
            ->FeedFilter($type)
            ->CreatedDateFilter($created_date)
            ->FeedOperation($action)
            ->GetAsArray();

    }

}
