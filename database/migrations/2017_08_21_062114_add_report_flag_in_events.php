<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportFlagInEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Model\Event::where('event_type', 'live')->Active()->update(['report_cron_flag' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \App\Model\Event::where('event_type', 'live')->Active()->unset('report_cron_flag');
    }
}
