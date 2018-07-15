<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;

class ProductTypeTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'product_type';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('id');
        });

        // General Product One
        DB::collection($collection)->insert([
            'id' => 1,
            'product_type' => 'Lms Program',
            'created_at' => time(),
            'updated_at' => time()

        ]);

        // General Product Two
        DB::collection($collection)->insert([
            'id' => 2,
            'product_type' => 'Channel',
            'created_at' => time(),
            'updated_at' => time()

        ]);

        // General Product Three
        DB::collection($collection)->insert([
            'id' => 3,
            'product_type' => 'Product',
            'created_at' => time(),
            'updated_at' => time()

        ]);

    }
}
