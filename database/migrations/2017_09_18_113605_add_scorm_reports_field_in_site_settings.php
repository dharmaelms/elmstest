<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScormReportsFieldInSiteSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $general = SiteSetting::where('module', '=', 'General')
                    ->where('setting.scorm_reports', 'exists', false)
                    ->get();
        if ($general->count() > 0) {
            SiteSetting::where('module', '=', 'General')->update([
                'setting.scorm_reports' => 'on'
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
                    ->where('setting.scorm_reports', 'exists', true)
                    ->get();
        if ($general->count() > 0) {
            SiteSetting::where('module', '=', 'General')->unset([
                'setting.scorm_reports'
            ]);
        }
    }
}
