<?php namespace App\Model\ImportLog\Entity;

use App\Model\Sequence;
use Moloquent;

class UserLog extends Moloquent
{
    
    protected $collection = "erp_user_log";

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
    public static function uniqueErpUserLogId()
    {
        return Sequence::getSequence('user_log');
    }

    /** This function used to insert user log
     *
     * @method getInsertErpUserLog
     * @param array $input
     */
    public static function insertErpUserLog($input)
    {
        $id = self::uniqueErpUserLogId();
        $log_id['rid'] = $id;
        $record = array_merge($log_id, $input);
        self::insert($record);
    }

    /** This function used to get ftp details
     *
     * @method getFtpDetails
     * @return resource a FTP stream on success or <b>FALSE</b> on error
     */
    public static function getFtpDetails()
    {
        $host = config('app.ftp_host');
        $user = config('app.ftp_username');
        $password = config('app.ftp_password');
        $conn_id = ftp_connect($host);
        ftp_login($conn_id, $user, $password);
        return $conn_id;
    }

    /** This function used to validate ftp details
     *
     * @method getValidateFtp
     * @return boolean
     */
    public static function getValidateFtp()
    {
        $host = config('app.ftp_host');
        $user = config('app.ftp_username');
        $password = config('app.ftp_password');
        $conn_id = ftp_connect($host);
        $login_result = false;
        if($conn_id){
            $login_result = ftp_login($conn_id, $user, $password);
            ftp_close($conn_id);
        }
        return (bool)$login_result;
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

    /** This function used to get user log records
     *
     * @method getUserExportRecords
     * @param string $type
     * @param string $created_date
     * @param string $action
     * @return array of log records
     */
    public static function getUserExportRecords($type = 'ALL', $created_date = null, $action = 'ALL')
    {
        return self::where(function ($query) {
            $query->where('type', 'exists', false);
        })
            ->FeedFilter($type)
            ->CreatedDateFilter($created_date)
            ->FeedAction($action)
            ->GetAsArray();

    }

    /** This function used to get user import count
     *
     * @method getUserImportCount
     * @param string $type
     * @param string $search
     * @param string $created_date
     * @param string $action
     * @return integer of user log records
     */
    public static function getUserImportCount($type = 'ALL', $search = null, $created_date = null, $action = 'ALL')
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

    /** This function used to get user log records
     *
     * @method  getUserImportRecords
     * @param string $type
     * @param int $start
     * @param int $limit
     * @param array $orderByArray
     * @param string $search
     * @param string $created_date
     * @param string $action
     * @return array of log records
     */
    public static function getUserImportRecords($type = 'ALL',
                                                $start = 0,
                                                $limit = 10,
                                                $orderByArray = ['created_at' => 'desc'],
                                                $search = null,
                                                $created_date = null,
                                                $action = 'ALL')
    {
        return self::where(function ($query) {
            $query->where('type', 'exists', false);
        })
            ->FeedSearch($search)
            ->FeedFilter($type)
            ->CreatedDateFilter($created_date)
            ->FeedAction($action)
            ->GetOrderBy($orderByArray)
            ->GetByPagination($start, $limit)
            ->GetAsArray();

    }
}
