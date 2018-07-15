<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;

class AttributesTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'attributes';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('attribute_id');
        });

        // Inserting default batchname
        DB::collection($collection)->insert([
            'attribute_id' => 1,
            'attribute_type' => 'batch',
            'attribute_name' => 'batchname',
            'attribute_label' => 'Batch Name',
            'visibility' => 1,
            'mandatory' => 1,
            'default' => 1,
            'ecommerce' => 1,
            'datatype' => 'text',
            'created_at' => time(),
            'updated_at' => time(),
            'unique' => 1
        ]);

        // Inserting default startdate
        DB::collection($collection)->insert([
            'attribute_id' => 2,
            'attribute_type' => 'batch',
            'attribute_name' => 'startdate',
            'attribute_label' => 'Start Date',
            'visibility' => 1,
            'mandatory' => 1,
            'default' => 1,
            'ecommerce' => 1,
            'datatype' => 'date',
            'created_at' => time(),
            'updated_at' => time(),
            'unique' => 1
        ]);

        // Inserting default enddate
        DB::collection($collection)->insert([
            'attribute_id' => 3,
            'attribute_type' => 'batch',
            'attribute_name' => 'enddate',
            'attribute_label' => 'End Date',
            'visibility' => 1,
            'mandatory' => 1,
            'default' => 1,
            'ecommerce' => 1,
            'datatype' => 'date',
            'created_at' => time(),
            'updated_at' => time(),
            'unique' => 1
        ]);
    }
}
