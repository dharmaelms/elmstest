<?php

namespace App\Services\Report;

use App\Model\CronLog;
use DB;
use MongoUpdateBatch;
use Exception;
use Log;

/**
 * Class MongoBulkInUpService
 * @package App\Services\Report
 */
class MongoBulkInUpService implements IMongoBulkInUpService
{
    /**
     * Log cron process status by modules
     *
     * @param string $module
     * @param string $status
     * @param int|null $id
     * @return int
     */
    public function cronLog($module, $status, $id = null)
    {
        if (is_null($id)) {
            return CronLog::getInsertData($module, $status);
        }
        return CronLog::getupdatedata($module, $status, $id);
    }

    /**
     * @param array $bulk_ary
     * @param string $tbl_name
     * @return bool
     */
    public function mongoBulkInsertProcess($bulk_ary, $tbl_name)
    {
        if (empty($bulk_ary)) {
            return false;
        }

        DB::collection($tbl_name)->raw(function ($collection) use ($bulk_ary) {
            return $collection->insertMany(array_values($bulk_ary), ['continueOnError' => true]);
        });
        unset($bulk_ary);
        return true;
    }

    /**
     * @param array $bulk_ary
     * @param string $tbl_name
     * @return bool
     */
    public function mongoBulkUpdateProcess($bulk_ary, $tbl_name)
     {   
        if (empty($bulk_ary)) {
            return false;
        }
        try {
            $batch = new \MongoDB\Driver\BulkWrite();
            foreach ($bulk_ary as $value) {
                $batch->update(array_get($value, '0'), array_get($value, '1'), array_get($value, '2'));
            }
            if (!empty(config('database.connections.mongodb.username'))) {
                $manager = new \MongoDB\Driver\Manager("mongodb://".config('database.connections.mongodb.username').":".config('database.connections.mongodb.password')."@".config('database.connections.mongodb.host')."/".config('database.connections.mongodb.database'));
            } else {
                $manager = new \MongoDB\Driver\Manager("mongodb://".config('database.connections.mongodb.host').":".config('database.connections.mongodb.port'));
            }
            $manager->executeBulkWrite(
                config('database.connections.mongodb.database') .'.'.$tbl_name, 
                $batch
            );
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
