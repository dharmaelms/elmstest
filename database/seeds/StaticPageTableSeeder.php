<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;

class StaticPageTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'staticpages';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('staticpagge_id');
        });

        DB::collection($collection)->insert([
            'staticpagge_id' => 1,
            'title' => 'Terms and Conditions',
            'slug' => 'terms-and-conditions',
            'metakey' => '',
            'meta_description' => '',
            'content' => '',
            'editor_images' => [],
            'status' => 'ACTIVE',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        DB::collection($collection)->insert([
            'staticpagge_id' => 2,
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'metakey' => '',
            'meta_description' => '',
            'content' => '',
            'editor_images' => [],
            'status' => 'ACTIVE',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

}


