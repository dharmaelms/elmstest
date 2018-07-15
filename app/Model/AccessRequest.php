<?php

namespace App\Model;

use Auth;
use Moloquent;

class AccessRequest extends Moloquent
{

    protected $table = 'access_request';
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public static function getAccessRequest()
    {
        return Sequence::getSequence('request_id');
    }

    public static function insertAccessRequests($program_id, $program_title, $program_slug)
    {
        $user_id = (int)Auth::user()->uid;
        $user_name = Auth::user()->username;
        $user_email = Auth::user()->email;
        $request_id = self::getAccessRequest();
        self::insert([
            'request_id' => $request_id,
            'program_id' => (int)$program_id,
            'program_title' => $program_title,
            'program_slug' => $program_slug,
            'user_id' => $user_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'created_at' => time(),
            'status' => 'PENDING',
        ]);
        Program::where('program_id', '=', (int)$program_id)->push('relations.access_request_pending', $user_id);
        return $request_id;
    }

    public static function accessRequestInfo($status = 'all', $start = 0, $limit = 10, $orderby = ['requested_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];
        if ($status == 'all') {
            if ($search) {
                return self::where('program_title', 'like', '%' . $search . '%')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } else {
            if ($search) {
                return self::where('program_title', 'like', '%' . $search . '%')->where('status', '=', $status)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('status', '=', $status)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
    }

    public static function accessRequestCount($status = 'all', $search = null)
    {
        if ($status == 'all') {
            if ($search) {
                return self::where('program_title', 'like', '%' . $search . '%')->count();
            } else {
                return self::count();
            }
        } else {
            if ($search) {
                return self::where('program_title', 'like', '%' . $search . '%')->where('status', '=', $status)->count();
            } else {
                return self::where('status', '=', $status)->count();
            }
        }
    }

    public static function requestAccessInfo($req_id)
    {
        return self::where('request_id', '=', (int)$req_id)->get()->toArray();
    }

    public static function grantAccess(
        $req_id,
        $status = "GRANTED",
        $access_mode = "assigned_by_admin",
        $type = "content_feed"
    ) {
        $request_info = self::requestAccessInfo($req_id);

        Program::where('program_id', '=', (int)$request_info[0]['program_id'])
            ->pull('relations.access_request_pending', (int)$request_info[0]['user_id']);

        Program::where('program_id', '=', (int)$request_info[0]['program_id'])
            ->push('relations.access_request_granted', (int)$request_info[0]['user_id'], true);

        Program::where('program_id', '=', (int)$request_info[0]['program_id'])
            ->push('relations.active_user_feed_rel', (int)$request_info[0]['user_id'], true);

        User::where('uid', '=', (int)$request_info[0]['user_id'])
            ->push('relations.user_feed_rel', (int)$request_info[0]['program_id']);

        /* Updating Transaction table */
        $trans_id = Transaction::uniqueTransactionId();
        //$programdetails = Program::getProgramDetailsByID($value)->toArray();
        $now = time();
        $email = $request_info[0]['user_email'];
        $transaction = [
            'DAYOW' => date('l', $now),
            'DOM' => (int)date('j', $now),
            'DOW' => (int)date('w', $now),
            'DOY' => (int)date('z', $now),
            'MOY' => (int)date('n', $now),
            'WOY' => (int)date('W', $now),
            'YEAR' => (int)date('Y', $now),
            'trans_level' => 'user',
            'id' => (int)$request_info[0]['user_id'],
            'created_date' => time(),
            'email' => $email,
            'trans_id' => (int)$trans_id,
            'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
            'access_mode' => $access_mode,
            'added_by' => Auth::user()->username,
            'added_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'created_at' => time(),
            'updated_at' => time(),
            'type' => 'subscription',
            'status' => 'COMPLETE',// This is transaction status
            'requested_by_user' => (int)$request_info[0]['user_id'],
        ];

        $transaction_details = [
            'trans_level' => 'user',
            'id' => (int)$request_info[0]['user_id'],
            'trans_id' => (int)$trans_id,
            'program_id' => (int)$request_info[0]['program_id'],
            'program_slug' => $request_info[0]['program_slug'],
            'type' => $type,
            'program_title' => $request_info[0]['program_title'],
            'duration' => [ // Using the same structure from duration master
                'label' => 'Forever',
                'days' => 'forever',
            ],
            'start_date' => '', // Empty since the duration is forever
            'end_date' => '', // Empty since the duration is forever
            'created_at' => time(),
            'updated_at' => time(),
            'status' => 'COMPLETE',
            'requested_by_user' => (int)$request_info[0]['user_id'],
        ];
        // Add record to user transaction table
        Transaction::insert($transaction);
        TransactionDetail::insert($transaction_details);

        self::where('request_id', '=', (int)$req_id)
            ->update([
                'status' => $status,
                'updated_at' => time(),
                'granted_by' => Auth::user()->username,
                'trans_id' => (int)$trans_id,
            ]);

        return $request_info[0];
    }

    public static function denyAccess($req_id)
    {
        $request_info = self::requestAccessInfo($req_id);
        Program::where('program_id', '=', (int)$request_info[0]['program_id'])->pull('relations.access_request_pending', (int)$request_info[0]['user_id']);

        self::where('request_id', '=', (int)$req_id)
            ->update([
                'status' => 'DENIED',
                'updated_at' => time(),
                'denied_by' => Auth::user()->username,
            ]);
        Program::where('program_id', '=', (int)$request_info[0]['program_id'])->push('relations.access_request_denied', (int)$request_info[0]['user_id']);

        return;
    }

    /* for UAR*/
    public static function getAccessRequestPosted($start = 0, $limit = 3)
    {
        return self::where('status', '=', 'PENDING')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get(['program_title', 'user_name'])
            ->toArray();
    }

    public static function getAccessRequestPostedCount()
    {
        return self::where('status', '=', 'PENDING')
            ->count();
    }

    public static function getAllAccessRequests($status = null)
    {
        if ($status) {
            return self::where('status', '=', $status)->get();
        } else {
            return self::get();
        }
    }

    public static function getAllAccessRequestsByProgramID($program_id = null, $status = null)
    {
        if ($status) {
            return self::where('program_id', '=', (int)$program_id)->where('status', '=', $status)->get();
        } else {
            return self::where('program_id', '=', (int)$program_id)->get();
        }
    }

    public static function getAccessReq($start = null, $end = null)
    {
        if (!is_null($start) && !is_null($end)) {
            return self::where('status', '=', 'PENDING')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        } else {
            return 0;
        }
    }

    /*for make dimension table*/
    public static function getLastDayAccessRequest()
    {

        /*$start_time = strtotime('yesterday midnight');
        $end_time = $start_time + 86400;

        return self::whereBetween('date', [$start_time, $end_time])
                    ->get()
                    ->toArray();*/
        return self::get()->toArray();
    }
}
