<?php namespace App\Services\Report;

interface IMongoBulkInUpService
{
    public function cronLog($module, $status, $id = null);

    public function mongoBulkInsertProcess($bulk_ary, $tbl_name);

    public function mongoBulkUpdateProcess($bulk_ary, $tbl_name);
}
