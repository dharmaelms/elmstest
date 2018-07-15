<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\SiteSetting;

class AddLHSSettingsRecordInSiteSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lhs_setting = SiteSetting::where('id', '=', (int)19)->get();
        if ($lhs_setting->count() == 0) {
             SiteSetting::insert([
             'id' => 19,
             'module' => 'LHSMenuSettings',
             'setting' => [
                'programs' => 'on',
                'my_activity' => 'on'
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
        $lhs_setting = SiteSetting::where('id', '=', (int)19)->get();
        if ($lhs_setting->count() > 0) {
            SiteSetting::where('id', (int)19)->delete();
        }
    }
}
