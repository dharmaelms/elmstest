<?php

use App\Model\User\Entity\UserEnrollment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSourceIdInUserEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('app.sso.usergroup_id')) {
            $enrollments = UserEnrollment::where('source_id', config('app.sso.usergroup_id'))->where('source_type', 'USER_GROUP')->where('status', 'ENROLLED')->where('entity_type', 'PROGRAM')->get();
            $enrollments->each(function ($enrollment) {
                UserEnrollment::where('id', (int)$enrollment->id)->update(['source_id' => (int)$enrollment->source_id]);
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
        if (config('app.sso.usergroup_id')) {
            $enrollments = UserEnrollment::where('source_id', config('app.sso.usergroup_id'))->where('source_type', 'USER_GROUP')->where('status', 'ENROLLED')->where('entity_type', 'PROGRAM')->get();
            $enrollments->each(function ($enrollment) {
                UserEnrollment::where('id', (int)$enrollment->id)->update(['source_id' => (int)$enrollment->source_id]);
            });
        }
    }
}
