<?php

return [
    'hbs_pcv' => [
        'driver'         => 'oracle',
        'tns'            => env('DB_HBS_PCV_TNS', ''),
        'host'           => env('DB_HBS_PCV_HOST', ''),
        'port'           => env('DB_HBS_PCV_PORT', '1521'),
        'database'       => env('DB_HBS_PCV_DATABASE', ''),
        'username'       => env('DB_HBS_PCV_USERNAME', ''),
        'password'       => env('DB_HBS_PCV_PASSWORD', ''),
        'charset'        => env('DB_HBS_PCV_CHARSET', 'AL32UTF8'),
        'prefix'         => env('DB_HBS_PCV_PREFIX', ''),
        'prefix_schema'  => env('DB_HBS_PCV_SCHEMA_PREFIX', ''),
        'edition'        => env('DB_EDITION', 'ora$base'),
        'server_version' => env('DB_SERVER_VERSION', '11g'),
    ],
];
