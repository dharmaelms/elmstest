<?php

return [
    /**
     * Elasticsearch configuration options
     */
    'params' => [
        'scheme' => env('ELASTIC_SCHEME'),
        'host' => env('ELASTIC_HOST'),
        'port' => env('ELASTIC_PORT'),
        'index' => env('ELASTIC_INDEX'),
        'username' => env('ELASTIC_USERNAME'),
        'password' => env('ELASTIC_PASSWORD'),
    ],
    'service' => env('ELASTIC_SERVICE', true),
];
