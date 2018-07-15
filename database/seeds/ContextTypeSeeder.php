<?php

use Illuminate\Database\Seeder;

use App\Model\RolesAndPermissions\Entity\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContextTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $collection = "contexts";

        // Removing existing collection
        Schema::drop($collection);

        DB::collection($collection)->insert(
            [
              "id" => Context::getNextSequence(),
              "name" => "System",
              "slug" => "system",
              "description" => "Roles that are created in System level will be available throughout the system."
            ]
        );

        DB::collection($collection)->insert(
            [
              "id" => Context::getNextSequence(),
              "name" => "Program",
              "slug" => "program",
              "description" => "Roles that are created in program level will be available only in Channel, Program,
                                Product modules."
            ]
        );

        DB::collection($collection)->insert(
            [
              "id" => Context::getNextSequence(),
              "name" => "Course",
              "slug" => "course",
              "description" => "Roles that are created in Course level will be available only in Course."
            ]
        );
 
        DB::collection($collection)->insert(
            [
              "id" => Context::getNextSequence(),
              "name" => "Batch",
              "slug" => "batch",
              "description" => "Roles that are created in batch level will be available only in batch."
            ]
        );
    }
}
