<?php

use App\Model\SiteSetting;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddAColumnUserSettingInSiteSettingSeeder
 */
class AddAColumnUserSettingInSiteSettingSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SiteSetting::where('id', '=', (int)17)->update(['setting' => ['nda_acceptance' => 'off']]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SiteSetting::where('id', (int)17)->delete();
    }
}
