<?php

use App\Model\User;
use App\Model\UserGroup;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSsoUsercountBug3626 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::whereIn('relations.active_usergroup_user_rel', [(int)config('app.sso.usergroup_id')])->where('status', 'ACTIVE')->pluck('uid')->all();
        if (!empty($users)) {
            UserGroup::where('ugid', (int)config('app.sso.usergroup_id'))->push('relations.active_user_usergroup_rel', $users, true);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $users = User::whereIn('relations.active_usergroup_user_rel', [(int)config('app.sso.usergroup_id')])->where('status', 'ACTIVE')->pluck('uid')->all();
        if (!empty($users)) {
            UserGroup::where('ugid', (int)config('app.sso.usergroup_id'))->push('relations.active_user_usergroup_rel', $users, true);
        }
    }
}
