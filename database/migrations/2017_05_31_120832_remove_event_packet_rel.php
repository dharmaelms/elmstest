<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Event;
use App\Model\Program;
use App\Model\Packet;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RemoveEventPacketRel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Event::where("relations.event_packet_rel", "exists", true)->where("status", "!=", "DELETED")->get()->each(function ($event) {
            $event_packet_rel = $event->relations["event_packet_rel"];
            if (is_array($event_packet_rel) && !empty($event_packet_rel)) {
                foreach ($event_packet_rel as $packet_id) {
                    try {
                        $packet = Packet::findOrFail($packet_id);
                        $program = Program::where("program_slug", $packet->feed_slug)->firstOrFail();
                        $event->push("relations.feed_event_rel.{$program->program_id}", $packet_id, true);
                        $event->unset("relations.event_packet_rel");
                    } catch (ModelNotFoundException $e) {
                        Log::error($e->getMessage());
                    }
                }
            }
        });
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
