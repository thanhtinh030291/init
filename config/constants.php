<?php
return[
    'appName' => 'Mobile Assistant',
    'appEmail' => env('MAIL_FROM_ADDRESS', 'admin@pacificcross.com.vn'),
    'appLogo'     => "/images/logo.png",
    'srcUpload'   => '/public/upload/',
    'srcStorage'  => '/storage/upload/',
    
    
    'paginator' => [
        'itemPerPage' => '10',
    ],
    'limit_list' => [
        10 => 10,
        20 => 20,
        30 => 30,
        40 => 40,
        50 => 50,
    ],
    'company' => [
        'pcv' => 'PCV',
        'bsh' => 'BSH',
        'fubon' => 'Fubon',
        'cathay' => 'Cathay',
    ],
    'min_age_use_app' => 16,
];