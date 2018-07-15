<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\MyActivity;
use App\Model\ScormActivity;

class InsertScormRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $myActivity =  MyActivity::where("module_id", "!=", null)
        ->where('scorm_runtime_activity_data.entry', 'exists', true)
        ->orWhere('scorm_runtime_activity_data.lesson_location', 'exists', true)
        ->orWhere('scorm_runtime_activity_data.lesson_status', 'exists', true)
        ->orWhere('scorm_runtime_activity_data.score_raw', 'exists', true)
        ->orWhere('scorm_runtime_activity_data.suspend_data', 'exists', true)
        ->get();
        if ($myActivity->count() > 0) {
            $myActivity->each(function($records){
                $scorm_id = $records->module_id;
                $scorm = ScormActivity::where('scorm_id', '=', $scorm_id)
                        ->where('packet_id', '=', $records->packet_id)
                        ->where('feed_id', '=', $records->feed_id)
                        ->where('user_id', '=', $records->user_id)
                        ->get();
                if ($scorm->count() == 0) {
                    $data = [
                        "user_id" => $records->user_id,
                        "date" => time(),
                        "scorm_name" => $records->module_name,
                        "scorm_id" => $records->module_id,
                        "packet_id" => $records->packet_id,
                        "packet_name" => $records->packet_name,
                        "feed_id" => $records->feed_id,
                        "feed_name" => $records->feed_name,
                        "user_full_name" => $records->scorm_runtime_activity_data["user_full_name"],
                        "total_time_spent" => $records->scorm_runtime_activity_data["total_time_spent"],
                        "entry" => $records->scorm_runtime_activity_data["entry"],
                        "lesson_location" => $records->scorm_runtime_activity_data["lesson_location"],
                        "lesson_status" => $records->scorm_runtime_activity_data["lesson_status"],
                        "score_raw" => $records->scorm_runtime_activity_data["score_raw"],
                        "suspend_data" => $records->scorm_runtime_activity_data["suspend_data"]
                    ];
                    ScormActivity::insert($data);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
