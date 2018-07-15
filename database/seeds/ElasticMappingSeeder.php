<?php

use App\Enums\Elastic\Types as ET;
use App\Libraries\Elastic\Elastic;
use Illuminate\Database\Seeder;

class ElasticMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $elastic = new Elastic;
        $index = config('elastic.params.index');
        $mappings = [
            "index" => $index,
            "body" => [
                "settings" => [
                    "analysis" => [
                        "filter" => [
                            "ngram_filter"=> [
                               "type"=> "ngram",
                               "min_gram"=> 2,
                               "max_gram"=> 20
                            ]
                        ],
                        "analyzer" => [
                            "ngram_analyzer"=> [
                               "type"=> "custom",
                               "tokenizer"=> "whitespace",
                               "filter"=> [
                                  "lowercase",
                                  "ngram_filter"
                               ]
                            ]
                        ]
                    ],
                    "number_of_shards" => 5,
                    "number_of_replicas" => 1,
                ],
                "mappings" => [
                    ET::PROGRAM => [
                        "_source" => [
                            "enabled" => true,
                        ],
                        "_all" => [
                            "enabled" => false,
                        ],
                        "properties" => [
                            "id" => [
                                "type" => "integer"
                            ],
                            "title" => [ //field:program_title
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                                "fields" => [
                                    "raw" => [
                                        "type" => "keyword",
                                    ]
                                ]
                            ],
                            "short_title" => [ //field:program_shortname
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "description" => [ //field:program_description
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "categories" => [ //field:program_categories
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "keywords" => [ //field:program_keywords
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "type" => [ //field:type
                                "type" => "text",
                            ],
                            "sub_type" => [ //field:type
                                "type" => "text",
                            ],
                            "user_ids" => [
                                "type" => "keyword",
                            ],
                            "slug" => [ //field:program_slug
                                "type" => "keyword",
                                "index" => true,
                            ],
                            "cover_image" => [ //field:program_cover_media
                                "type" => "text",
                                "index" => "no",
                            ],
                        ]
                    ],
                    ET::PACKAGE => [
                        "_source" => [
                            "enabled" => true,
                        ],
                        "_all" => [
                            "enabled" => false,
                        ],
                        "properties" => [
                            "id" => [
                                "type" => "integer"
                            ],
                            "title" => [ //field:program_title
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                                "fields" => [
                                    "raw" => [
                                        "type" => "keyword",
                                    ]
                                ]
                            ],
                            "short_title" => [ //field:program_shortname
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "description" => [ //field:program_description
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "categories" => [ //field:program_categories
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "keywords" => [ //field:program_keywords
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "user_ids" => [
                                "type" => "keyword",
                            ],
                            "slug" => [ //field:program_slug
                                "type" => "keyword",
                                "index" => true,
                            ],
                            "cover_image" => [ //field:program_cover_media
                                "type" => "text",
                                "index" => "no",
                            ],
                        ]
                    ],
                    ET::POST => [
                        "_source" => [
                            "enabled" => true,
                        ],
                        "_all" => [
                            "enabled" => false,
                        ],
                        "properties" => [
                            "program_id" => [ //field:id
                                "type" => "integer",
                            ],
                            "id" => [
                                "type" => "integer"
                            ],
                            "title" => [ //field:packet_title
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "description" => [ //field:packet_description
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "sequential" => [ //field:sequential_access
                                "type" => "boolean"
                            ],
                            "slug" => [ //field:packet_slug
                                "type" => "keyword",
                                "index" => true,
                            ],
                            "program_slug" => [ //field:feed_slug
                                "type" => "keyword",
                                "index" => true,
                            ],
                            "cover_image" => [ //field:packet_cover_media
                                "type" => "text",
                                "index" => "no",
                            ],
                            "user_ids" => [ //field:inherit:program:user_ids
                                "type" => "keyword",
                            ],
                        ]
                    ],
                    ET::ITEM => [
                        "_source" => [
                            "enabled" => true,
                        ],
                        "_all" => [
                            "enabled" => false,
                        ],
                        "properties" => [
                            "id" => [ //field:id
                                "type" => "integer",
                            ],
                            "program_id" => [ //field:id
                                "type" => "integer",
                            ],
                            "post_id" => [
                                "type" => "integer"
                            ],
                            "title" => [ //field:name
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "description" => [ //field:display_name
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "type" => [ //field:type
                                "type" => "text",
                            ],
                            "sequential" => [
                                "type" => "boolean"
                            ],
                            "slug" => [ //field:packet_slug
                                "type" => "keyword",
                                "index" => true,
                            ],
                            "program_slug" => [ //field:feed_slug
                                "type" => "keyword",
                                "index" => true,
                            ],
                            "user_ids" => [ //field:inherit:program:user_ids
                                "type" => "keyword",
                            ],
                        ]
                    ],
                    ET::ASSESSMENT => [
                        "_source" => [
                            "enabled" => true,
                        ],
                        "_all" => [
                            "enabled" => false,
                        ],
                        "properties" => [
                            "id" => [ //field:id
                                "type" => "integer",
                            ],
                            "title" => [ //field:name
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "description" => [ //field:display_name
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "type" => [ //field:type
                                "type" => "text",
                            ],
                            "keywords" => [ //field:program_keywords
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "user_ids" => [ //field:inherit:program:user_ids
                                "type" => "keyword",
                            ],
                        ]
                    ],
                    ET::EVENT => [
                        "_source" => [
                            "enabled" => true,
                        ],
                        "_all" => [
                            "enabled" => false,
                        ],
                        "properties" => [
                            "id" => [ //field:id
                                "type" => "integer",
                            ],
                            "title" => [ //field:name
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "description" => [ //field:display_name
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            "type" => [ //field:type
                                "type" => "text",
                            ],
                            "keywords" => [ //field:program_keywords
                                "type" => "text",
                                "analyzer" => "ngram_analyzer",
                                "search_analyzer" => "whitespace",
                            ],
                            'start_time' => [
                                'type' => 'integer'
                            ],
                            "user_ids" => [ //field:inherit:program:user_ids
                                "type" => "keyword",
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $elastic->mapping($mappings);
    }
}
