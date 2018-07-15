<?php

use App\Model\QuizAttempt;
use App\Model\MyActivity;
use App\Model\Program;
use App\Model\Packet;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\Quiz;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPastAttemptClosedActivityForSubscribedPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attempted_quizzes = QuizAttempt::where('status', '=', 'CLOSED')
            ->get(['user_id', 'quiz_id', 'completed_on', 'status']);
        $attempted_users =  $attempted_quizzes->lists('user_id')->unique();
        $attempted_quiz_ids =  $attempted_quizzes->lists('quiz_id')->unique();
        $users_details = User::whereIn('uid', $attempted_users->toArray())
            ->get(['uid', 'subscription', 'relations'])
            ->keyBy('uid')
            ->toArray();
        $ug_ids = array_where(
            array_flatten(array_pluck($users_details, 'relations.active_usergroup_user_rel')),
            function ($value, $key) {
                return !is_null($key);
            }
        );
        $ugs_details = UserGroup::whereIn('ugid', $ug_ids)->get(['ugid', 'relations'])
            ->keyBy('ugid');
        $quizzes_details = Quiz::whereIn('quiz_id', $attempted_quiz_ids->toArray())
            ->where('relations.feed_quiz_rel', '!=', [])
            ->where('relations.feed_quiz_rel', 'exists', 'true')
            ->get(['quiz_id', 'relations.feed_quiz_rel', 'quiz_name'])->keyBy('quiz_id');
        $data = [];
        foreach ($attempted_quizzes as $value) {
            $user_id = (int)$value->user_id;
            $quiz_id = (int)$value->quiz_id;
            $date = $value->completed_on;
            $user_details = array_get($users_details, $user_id, []);
            $quiz_details = array_get($quizzes_details, $quiz_id, []);
            if (empty($user_details) || empty($quiz_details)) {
                continue;
            }
            $quiz_name = $quiz_details->quiz_name;
            $quiz_feed_rel = array_get($quiz_details->relations, 'feed_quiz_rel', []);
            $post_ids = array_flatten($quiz_feed_rel);
            if (empty($post_ids)) {
                continue;
            }
            $user_ug_ids = array_get($user_details, 'relations.active_usergroup_user_rel', []);
            $user_feed_rel =  array_get($user_details, 'relations.user_feed_rel', []);
            $user_cource_rel =  array_get($user_details, 'relations.user_course_rel', []);
            $user_pack_rel =  array_get($user_details, 'relations.user_package_feed_rel', []);
            $all_fedd_ug_rell = $all_cource_ug_rell = $all_pack_ug_rell = $subscribed_pids = [];
            $subs_programs = array_get($user_details, 'subscription', []);
            $subscribed_pids = array_pluck($subs_programs, 'program_id');
            foreach ($user_ug_ids as $user_ug_id) {
                $ug_details = $ugs_details->get((int)$user_ug_id);
                $all_fedd_ug_rell[] = array_get($ug_details->relations, 'usergroup_feed_rel', []);
                $all_cource_ug_rell[] = array_get($ug_details->relations, 'usergroup_course_rel', []);
                $all_pack_ug_rell[] = array_get($ug_details->relations, 'usergroup_child_feed_rel', []);
            }
            $all_fedd_ug_rell = array_flatten($all_fedd_ug_rell);
            $all_cource_ug_rell = array_flatten($all_cource_ug_rell);
            $all_pack_ug_rell = array_flatten($all_pack_ug_rell);
            $user_feed_rel = array_merge(
                $user_feed_rel,
                $user_cource_rel,
                $user_pack_rel,
                $all_fedd_ug_rell,
                $all_cource_ug_rell,
                $all_pack_ug_rell,
                $subscribed_pids
            );
            $user_feed_rel = array_unique($user_feed_rel);
            if (!empty(array_intersect(array_keys($quiz_feed_rel), $user_feed_rel))) {
                foreach ($quiz_feed_rel as $channel_id => $specific_channel_rel) {
                    $feedName = Program::pluckFeedNameByID($channel_id);
                    if (!in_array($channel_id, $user_feed_rel)) {
                        continue;
                    }
                    if (is_array($specific_channel_rel)) {
                        foreach ($specific_channel_rel as $post_id) {
                            $post_deatils = Packet::getPacketByID((int)$post_id);
                            if (empty($post_deatils)) {
                                continue;
                            }
                            $packet = $post_deatils[0];

                            $data = [
                                'DAYOW' => $date->format('l'),
                                'DOM' => (int)$date->day,
                                'DOW' => (int)$date->dayOfWeek,
                                'DOY' => (int)$date->dayOfYear,
                                'MOY' => (int)$date->month,
                                'WOY' => (int)$date->weekOfYear,
                                'YEAR' => (int)$date->year,
                                'user_id' => (int)$user_id,
                                'module' => 'element',
                                'action' => 'attempt_closed',
                                'module_name' => $quiz_name,
                                'module_id' => (int)$quiz_id,
                                'element_type' => 'assessment',
                                'packet_id' => (int)$packet['packet_id'],
                                'packet_name' => $packet['packet_title'],
                                'feed_id' => (int)$channel_id,
                                'feed_name' => $feedName,
                                'url' => 'assessment/detail/' . $quiz_id,
                                'date' => $date->getTimestamp()
                            ];
                            MyActivity::insert($data);
                        }
                    }
                }
            }
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
