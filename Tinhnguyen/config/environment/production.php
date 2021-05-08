<?php

define('ROOT_FOLDER', '/');

define('DEBUG', false);
define('DEBUG_AUTOLOAD', false);
define('DEBUG_ERROR', false);
define('DEBUG_QUERY', false);
define('DEBUG_SMTP', false);

define('CHARSET', 'utf-8');
define('POST_SIZE', '64M');
define('RAM_LIMIT', -1);
define('TIMEOUT', 0);

define('FACEBOOK_ID', '263108050694674');

define('GOOGLE_RECAPTCHA_SITE_KEY', '6LdT3SYTAAAAABtO-CgKAV8Ec1-seJ0NLvCx9-ER');
define('GOOGLE_RECAPTCHA_SECRET_KEY', '6LdT3SYTAAAAACTMiP1OJZD0_ITUKvwmptsleAPT');

define('JWT_SECRET_KEY', 'cb63a03774fdf986');

define('STORE_SESSION_IN_DATABASE', true);
define('SESSION_SAVE_PATH', dirname(dirname(__DIR__)) . '/temp/cch/session');
define('SESSION_LIFETIME', 7200);

define('SHOW_LAST_MODIFIED', false);

define('PASSWORD_EXPIRED_PERIOD', '6 months');

define('FORCE_ADMIN_SSL', true);
define('FORCE_SYSTEM_SSL', true);
define('FORCE_RESTFUL_SSL', true);
define('FORCE_CLIENT_SSL', true);
define('FORCE_TASK_RUN', false);

define('HTTP_REQUEST_TRY_INTERVAL', '+5 minutes');

define('SEND_EMAIL', true);
define('REQUEST_SEND_LIMIT', 0);
define('EMAIL_SEND_LIMIT', 0);
define('EMAIL_SEND_TRY', 10);
define('SUPPORT_EMAIL', 'nghiemle@pacificcross.com.vn');
define('SUPPORT_EMAIL_2', 'nghiemle@pacificcross.com.vn');

define('SEND_SMS', true);
define('SMS_API', 'http://app.sms.fpt.net/');
define('SMS_CLIENT_ID', '9e1fFba27Cf47ef6dF65e8ae837359f80d628268');
define('SMS_SECRET', 'c653183a7879cdf62edb8abd87d1ef7d51d628fb47b8a02d2e88cdc539a3Ca4f7244b323');
define('SMS_FALLBACK', '');
define('SMS_SEND_LIMIT', 0);
define('SMS_SEND_TRY', 0);

define('PUSH_NOTIFICATION', true);
define('PUSH_NOTIFICATION_API_KEY', 'AAAAje4dCAk:APA91bGRldbpziGIKNzXMYmT9Xs7lAA-hsl-9n6Jv0--xAq_NcbGhYmLzfZu1ZpUoS39SIPWcKKfsDDRfYSXPjoJewhpbdvlL3ARauxnCOlF6tfpnlhglAdnYuQEZNI0L9J3_xpIrEJD');

define('MAIN_DATABASE', 'main');
define('TRANSACTION', true);
define('DATABASES', json_encode([
    'main' => [
        'database_info' => [
            'type' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'name' => 'card_validation_2',
            'user' => 'card_validation',
            'pass' => 'SiBat6bUwttBo8jG',
            'chst' => 'utf8',
            'emulate_prepares' => true
        ],
        'table_info' => [
            'pkey' => 'id',
            'fkey' => '%s_id',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
            "SET SESSION group_concat_max_len = 1000000"
        ]
    ],
    'main_website' => [
        'database_info' => [
            'type' => 'mysql',
            'host' => '52.163.53.173',
            'port' => '3306',
            'name' => 'zadmin_pacificcross',
            'user' => 'geo_user',
            'pass' => 'Spp6znkG6biRAvX',
            'chst' => 'utf8',
            'cert' => '52.163.53.173.pem',
            'verify' => false,
            'emulate_prepares' => false
        ],
        'table_info' => [
            'pkey' => 'id',
            'fkey' => '%s_id',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
            "SET SESSION group_concat_max_len = 1000000"
        ]
    ],
    'mantis_pcv' => [
        'database_info' => [
            'type' => 'mysql',
            'host' => '13.67.94.31',
            'port' => '3306',
            'name' => 'mantis_pcv',
            'user' => 'mantis_pcv',
            'pass' => 'pIa9CDHJ2qCtIs0i',
            'chst' => 'utf8',
            'emulate_prepares' => false
        ],
        'table_info' => [
            'pkey' => 'id',
            'fkey' => '%s_id',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
            "SET SESSION group_concat_max_len = 1000000"
        ]
    ],
    'mantis_fubon' => [
        'database_info' => [
            'type' => 'mysql',
            'host' => '13.67.94.31',
            'port' => '3306',
            'name' => 'mantis_fubon',
            'user' => 'mantis_fubon',
            'pass' => 'wCnnELLYcmP3YmLK',
            'chst' => 'utf8',
            'emulate_prepares' => false
        ],
        'table_info' => [
            'pkey' => 'id',
            'fkey' => '%s_id',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
            "SET SESSION group_concat_max_len = 1000000"
        ]
    ],
    'mantis_cathay' => [
        'database_info' => [
            'type' => 'mysql',
            'host' => '13.67.94.31',
            'port' => '3306',
            'name' => 'mantis_cathay',
            'user' => 'mantis_cathay',
            'pass' => 'B2zInZBOP8kzqAxf',
            'chst' => 'utf8',
            'emulate_prepares' => false
        ],
        'table_info' => [
            'pkey' => 'id',
            'fkey' => '%s_id',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
            "SET SESSION group_concat_max_len = 1000000"
        ]
    ],
    'hbs_pcv' => [
        'database_info' => [
            'type' => 'oracle',
            'host' => '192.168.148.4',
            'port' => '1521',
            'name' => 'VPROD',
            'user' => 'hbs',
            'pass' => 'colial2005',
            'chst' => 'utf8'
        ],
        'table_info' => [
            'pkey' => '%s_oid',
            'fkey' => '%s_oid',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "ALTER SESSION SET nls_date_format = 'YYYY-MM-DD'"
        ]
    ],
    'hbs_fubon' => [
        'database_info' => [
            'type' => 'oracle',
            'host' => '14.161.13.100',
            'port' => '1521',
            'name' => 'fubonprod',
            'user' => 'c##hbs',
            'pass' => 'Pacific235NVC',
            'chst' => 'utf8'
        ],
        'table_info' => [
            'pkey' => '%s_oid',
            'fkey' => '%s_oid',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "ALTER SESSION SET nls_date_format = 'YYYY-MM-DD'"
        ]
    ],
    'hbs_cathay' => [
        'database_info' => [
            'type' => 'oracle',
            'host' => '14.161.13.100',
            'port' => '1523',
            'name' => 'cathayprod',
            'user' => 'c##hbs',
            'pass' => 'Pacific235NVCCathay',
            'chst' => 'utf8'
        ],
        'table_info' => [
            'pkey' => '%s_oid',
            'fkey' => '%s_oid',
            'tnme' => '%s',
            'prfx' => ''
        ],
        'options' => [
            "ALTER SESSION SET nls_date_format = 'YYYY-MM-DD'"
        ]
    ]
]));

define('MOBILE_DEFAULT_PASSWORD', false);
define('MOBILE_ALLOW_WITHOUT_EMAIL', false);
define('MOBILE_ALLOW_WITHOUT_TEL', false);

define('CC_ETALK_URL', 'https://coverage-confirmation.pacificcross.com.vn');

define('PCV_ETALK_URL', 'https://pcv-etalk.pacificcross.com.vn');
define('PCV_ETALK_API_URL', PCV_ETALK_URL . '/api/rest');
define('PCV_ETALK_API_TOKEN', 'D-LTn2ojZ5Bp8IompeZOCG41VlpSMOCv');

define('FUBON_ETALK_URL', 'https://fubon-etalk.pacificcross.com.vn');
define('FUBON_ETALK_API_URL', FUBON_ETALK_URL . '/api/rest');
define('FUBON_ETALK_API_TOKEN', 'FP5c0DeurQAFWvH7Ix0aciY4KnG8Z0d9');

define('CATHAY_ETALK_URL', 'https://cathay-etalk.pacificcross.com.vn');
define('CATHAY_ETALK_API_URL', CATHAY_ETALK_URL . '/api/rest');
define('CATHAY_ETALK_API_TOKEN', 'Q-pHwrKGRixhNOlz6PMep1sdBekdNNj_');

define('LOGGER_APP_NAME', 'MobileApp');
define('LOGGER_TOKEN', 'b5a7cbeb-e35b-4ce7-97a6-f8c2e516dd2f/tag/app');

define('URL_API_EKYC', 'https://demo.computervision.com.vn');
define('KEY_API_EKYC', '61cafa5c6a6148159935b07045ecfa7b');
define('SECRET_API_EKYC', 'e22cd40b5ac5268d05a0aafd5453552e53d5a4d596858057a34b0c0cd4fdd096');


