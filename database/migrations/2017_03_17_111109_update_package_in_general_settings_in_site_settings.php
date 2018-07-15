<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\SiteSetting;

class UpdatePackageInGeneralSettingsInSiteSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $packages = SiteSetting::where('module', '=', 'General')->where('setting.packages', 'exists', true)->get();
        if($packages->count() > 0) {
            SiteSetting::where('module', '=', 'General')->unset(['setting.packages']);
        }

        $package = SiteSetting::where('module', '=', 'General')->where('setting.package', 'exists', false)->get();
        if ($package->count() > 0) {
            SiteSetting::where('module', '=', 'General')->update(['setting.package' => 'off']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $package = SiteSetting::where('module', '=', 'General')->where('setting.package', 'exists', true)->get();
        if($package->count() > 0) {
            SiteSetting::where('module', '=', 'General')->unset(['setting.package']);
        }

        $packages = SiteSetting::where('module', '=', 'General')->where('setting.packages', 'exists', false)->get();
        if ($packages->count() > 0) {
            SiteSetting::where('module', '=', 'General')->update(['setting.packages' => 'off']);
        }
    }
}
