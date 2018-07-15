<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Event;
class InsertDisplayNameInEventRecordings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $events = Event::where('recordings', 'exists', true)
            ->where('recordings', '!=', [])
            ->where('status', '!=', 'DELETED')
            ->get(['event_id', 'recordings']);
        foreach ($events as $value) {
            $i = 0;
            $data = [];
            foreach ($value->recordings as $val) {
                $i++;
                $val['display_name'] = trans('admin/event.recording').''.$i;
                $val['display_id'] = $i;
                $data[] = $val;
            }
            Event::where('event_id', $value->event_id)->update(['recordings' => $data]);
        }
        echo 'Events Recordings are updated Successfully.';
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $events = Event::where('recordings', 'exists', true)
            ->where('recordings', '!=', [])
            ->where('status', '!=', 'DELETED')
            ->get(['event_id', 'recordings']);
        foreach ($events as $value) {
            $i = 0;
            $data = [];
            foreach ($value->recordings as $val) {
                if ((array_key_exists('display_name', $val)) && (array_key_exists('display_id', $val))) {
                    unset($val['display_name'], $val['display_id']);
                    $data[] = $val;
                }
            }
            Event::where('event_id', $value->event_id)->update(['recordings' => $data]);
        }
        echo 'Events Recordings are updated Successfully in down process.';
    }
}
