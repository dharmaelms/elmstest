<?php

use App\Model\SiteSetting;
use Illuminate\Database\Migrations\Migration;

class AddAColumnQuizRemindersInSiteSettingSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $quiz_reminder =  SiteSetting::where('id', '=', (int)18)->get();
        if ($quiz_reminder->count() == 0) {
            SiteSetting::insert([
            'id' => 18,
              'module' => 'QuizReminders',
              'setting' => [
                 'Reminder1' => [
                    'reminder_status' => 'on',
                    'reminder_day' => 3,
                    'quiz_type' => [
                        'general' => 'on',
                        'general_practice' => 'off',
                        'question_generator' => 'on',
                    ],
                    'notify_by_mail' => 'off'
                 ],
                 'Reminder2' => [
                    'reminder_status' => 'off',
                    'reminder_day' => 1,
                    'quiz_type' => [
                        'general' => 'on',
                        'general_practice' => 'off',
                        'question_generator' => 'on',
                    ],
                    'notify_by_mail' => 'off'
                 ]
              ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SiteSetting::where('id', (int)18)->delete();
    }
}
