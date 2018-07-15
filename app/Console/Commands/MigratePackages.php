<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Package\Entity\Package;
use App\Model\Catalog\Pricing\Entity\Price;
use App\Services\UserGroup\IUserGroupService;
use App\Model\Program;
use App\Model\TransactionDetail;
use App\Model\Dam;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigratePackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate packages collection.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
         $usergroupService = App::make(IUserGroupService::class);

        Schema::create(
            'package_migration_log',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('program_id');
                $table->integer('package_id');
                $table->timestamps();
            }
        );

        $program = Program::Where('program_sub_type', 'collection')
                            ->where('program_type', 'content_feed')
                            ->get();
        $program->each(
            function ($program) use ($usergroupService) {

                $package_data = [
                    'package_id' => Package::getNextSequence(),
                    'package_title' =>  $program->program_title,
                    'title_lower' => isset($program->title_lower) ? $program->title_lower : '',
                    'package_shortname' => isset($program->program_shortname) ? $program->program_shortname: '',
                    'package_slug' => $program->program_slug,
                    'package_description' =>  isset($program->program_description) ? $program->program_description : '',
                    'package_startdate' => $program->program_startdate->timestamp,
                    'package_enddate' => $program->program_enddate->timestamp,
                    'package_display_startdate' => $program->program_display_startdate->timestamp,
                    'package_display_enddate' =>  $program->program_display_enddate->timestamp,
                    'package_duration' =>  isset($program->program_duration) ? $program->program_duration : '',
                    'package_review' => isset($program->program_review) ? $program->program_review : '',
                    'package_rating' => isset($program->program_rating) ? $program->program_rating : '',
                    'package_visibility' => isset($program->program_visibility) ? $program->program_visibility : '',
                    'package_keywords' => isset($program->program_keyword) ? $program->program_keyword : '',
                    'package_cover_media' => isset($program->program_cover_media) ? $program->program_cover_media : '',
                    'package_sellability' => $program->program_sellability,
                    'package_access' =>  isset($program->program_access)? $program->program_access : 'restricted_access',
                    'duration' => isset($program->duration) ? $program->duration : '',
                    'tabs' => isset($program->tabs) ? $program->tabs : '',
                    'last_activity' => isset($program->last_activity) ? $program->last_activity->timestamp : '',
                    'status' => $program->status,
                    'created_by' => isset($program->created_by) ? $program->created_by : '',
                    'created_by_name' => isset($program->created_by_name) ? $program->created_by_name : '',
                    'created_at' => isset($program->created_at) ? $program->created_at->timestamp : '',
                    'updated_at' => isset($program->updated_at) ? $program->updated_at->timestamp : '',
                    'updated_by' => isset($program->updated_by) ? $program->updated_by : '',
                    'promocode' => isset($program->promocode) ? $program->promocode : ''

                ];


                $package = new Package();
                $package->fill($package_data);
                $package->save();

                DB::table('package_migration_log')->insert(
                    [
                        'program_id' => (int)$program->program_id,
                        'package_id' => (int)$package->package_id
                    ]
                );

                TransactionDetail::where('package_id', (int)$program->program_id)
                                ->where('program_sub_type', 'collection')
                                ->update(['package_id' => (int)$package->package_id]);


                if (isset($program->relations)) {

                    $user_group_ids = array_get($program->relations, 'active_usergroup_feed_rel', []);

                    $user_ids = array_get($program->relations, 'active_user_feed_rel', []);

                    if (!empty($user_ids)) {
                        $package->user()->attach($user_ids);
                    } else {
                        Log::info('given program active_user_feed_rel not found = '.$program->program_id);
                    }

                    if (!empty($user_group_ids)) {
                        $package->userGroup()->attach($user_group_ids);
                    } else {
                        Log::info('given program active_user_feed_rel not found = '.$program->program_id);
                    }

                } else {
                    Log::info('given program active_usergroup_feed_rel not found = '.$program->program_id);
                }


                if (isset($program->child_relations)) {

                    $program_relation = array_get($program->child_relations, 'active_channel_rel', []);
                    if (!empty($program_relation)) {
                        $package->programs()->attach($program_relation);
                    } else {
                        Log::info('given parent program active_channel_rel not found = '.$program->program_id);
                    }

                } else {
                    Log::info('given program child_relations not found = '.$program->program_id);
                }

                $program_category_ids = isset($program->program_categories) ? $program->program_categories : [];

                if (!empty($program_category_ids)) {
                    $package->category()->attach($program_category_ids);
                }

                if ($program->program_sellability == "yes") {
                    Price::where('sellable_id', (int)$program->program_id)
                            ->update(['sellable_type' => 'package', 'sellable_id' =>  $package->package_id]);
                }

                if (isset($program->program_cover_media) && !empty($program->program_cover_media)) {
                        Dam::updateDAMSRelation($program->program_cover_media, 'package_media_rel', (int)$package->package_id);
                }
            }
        );

        echo "Migration completed";
    }
}
