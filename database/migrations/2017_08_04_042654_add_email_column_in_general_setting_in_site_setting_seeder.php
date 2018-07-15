<?php
use App\Model\SiteSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailColumnInGeneralSettingInSiteSettingSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SiteSetting::where('module', '=', 'General')
        ->where('setting.email_for_add_user', 'exists', false)
        ->update(['setting.email_for_add_user' => 'off']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SiteSetting::where('module', '=', 'General')
        ->where('setting.email_for_add_user', 'exists', true)
        ->unset(['setting.email_for_add_user']);
    }
}
