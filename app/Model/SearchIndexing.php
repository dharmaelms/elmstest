<?php


namespace App\Model;

use Moloquent;

class SearchIndexing extends Moloquent
{
    protected $table = 'search_indexing';

    public static function getUpdateIndexing($doc_type, $record_id)
    {
        // SearchIndexing::insert(array(
        //     'doc_type'=>$doc_type,
        //     'id'=>(int)$record_id,
        //     'created_at'=>time(),
        //     'status'=>'PENDING',
        //     ));

        // return;
    }
}
