<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Events\Auth\Registered;
use App\Model\User;
use App\Model\RolesAndPermissions\Entity\UserRoleAssignment;

class InsertMissingRecordsInUserRoleAssignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::get()->each(
                function ($user) {

            $user_array = $user->toArray();
            $uid = array_get($user_array, "uid");
            $role = array_get($user_array, "role");
            $userRoleAssignment = UserRoleAssignment::where('user_id', '=', $uid)
                                    ->where('instance_id', '=', null)
                                    ->get()->toArray();
            if (empty($userRoleAssignment)) {
                event(new Registered($uid, $role));
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
