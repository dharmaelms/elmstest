<?php

use App\Enums\User\NDAStatus as NDA;
use App\Model\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddNdaFieldToUsers
 */
class AddNdaFieldToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::where('status', '!=', 'DELETED')->get();
        $users->each(function ($user) {
            User::where('uid', '=', $user->uid)->update(['nda_status' => NDA::NO_RESPONSE]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $users = User::where('status', '!=', 'DELETED')->get();
        $users->each(function ($user) {
            User::where('uid', '=', $user->uid)->unset(['nda_status', 'nda_response_time']);
        });
    }
}
