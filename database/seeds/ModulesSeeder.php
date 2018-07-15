<?php

use Illuminate\Database\Seeder;
use App\Model\Module\Entity\Module;
use Illuminate\Support\Facades\DB;

use App\Enums\Module\Module as ModuleEnum;
use Illuminate\Support\Facades\Schema;

class ModulesSeeder extends Seeder
{
    /**
     * Define collection associated with the seeder
     *
     * @var string $collection
     */
    private $collection = "modules";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Removing existing collection
        Schema::drop($this->collection);

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "User", "slug" => ModuleEnum::USER]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "User Group", "slug" => ModuleEnum::USER_GROUP]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Role", "slug" => ModuleEnum::ROLE]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Channels", "slug" => ModuleEnum::CHANNEL]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Packages", "slug" => ModuleEnum::PACKAGE]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Category", "slug" => ModuleEnum::CATEGORY]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "DAMs", "slug" => ModuleEnum::DAMS]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Event", "slug" => ModuleEnum::EVENT]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Assessment", "slug" => ModuleEnum::ASSESSMENT]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Announcement", "slug" => ModuleEnum::ANNOUNCEMENT]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Report", "slug" => ModuleEnum::REPORT]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Course", "slug" => ModuleEnum::COURSE]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "FlashCard", "slug" => ModuleEnum::FLASHCARD]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "ManageSite", "slug" => ModuleEnum::MANAGE_SITE]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "e-Commerce", "slug" => ModuleEnum::E_COMMERCE]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "HomePage", "slug" => ModuleEnum::HOME_PAGE]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Country", "slug" => ModuleEnum::COUNTRY]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "ERP", "slug" => ModuleEnum::ERP]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Survey", "slug" => ModuleEnum::SURVEY]
        );

        DB::collection($this->collection)->insert(
            ["id" => Module::getNextSequence(), "name" => "Assignment", "slug" => ModuleEnum::ASSIGNMENT]
        );
    }
}
