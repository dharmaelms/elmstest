<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\SiteSetting;

class UpdateGeneralSettingsRecordInSiteSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $general = SiteSetting::where('module', '=', 'General')
                    ->where('setting.events', 'exists', false)
                    ->orwhere('setting.assessments', 'exists', false)
                    ->orwhere('setting.moodle_courses', 'exists', false)
                    ->get();
        if ($general->count() > 0) {
            SiteSetting::where('module', '=', 'General')->update([
                'setting.events' => 'on',
                'setting.assessments' => 'on',
                'setting.moodle_courses' => 'off'
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
       $general = SiteSetting::where('module', '=', 'General')
                    ->where('setting.events', 'exists', true)
                    ->orwhere('setting.assessments', 'exists', true)
                    ->orwhere('setting.moodle_courses', 'exists', true)
                    ->get();
        if ($general->count() > 0) {
            SiteSetting::where('module', '=', 'General')->unset([
                'setting.events','setting.assessments','setting.moodle_courses'
            ]);
        } 
    }
}
