<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Quiz;
use App\Model\User;

class UpdateUserQuizRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Quiz::where("status", "!=", "DELETED")->get()->each(function ($quiz) {
            $relations = $quiz->relations;
            $assigned_users = array_get($relations, "active_user_quiz_rel", []);
            if (!empty($assigned_users)) {
                $user_ids_to_remove = [];
                foreach ($assigned_users as $user_id) {
                    $user = User::where("uid", $user_id)
                            ->where("relations.user_quiz_rel", $quiz->quiz_id)->first();
                    if (is_null($user)) {
                        array_push($user_ids_to_remove, $user_id);
                    }
                }

                if (!empty($user_ids_to_remove)) {
                    $quiz->pull("relations.active_user_quiz_rel", $user_ids_to_remove);
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
