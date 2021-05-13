<?php
return[
    'appName' => 'Mobile Assistant',
    'appEmail' => env('MAIL_FROM_ADDRESS', 'admin@pacificcross.com.vn'),
    'debugEmail' => 'thanhtinh030291@gmail.com',
    'appLogo'     => "/img/logo.png",
    'srcUpload'   => '/public/upload/',
    'srcStorage'  => '/storage/upload/',
    'photoUpload'   => '/public/photo/',
    'photoStorage'  => '/storage/photo/',
    'majorityAge' => 23,
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
    'mess_match_en' => [
        0	=> "Successful",
        1	=> "The ID card photo is not existed",
        2	=> "The picture is a photocopy version of the id card",
        3	=> "The ID card photo is suspected of tampering",
        4	=> "The ID card photo does not contain a face",
        5	=> "The portrait photo does not contain a face",
        6	=> "Photo contains more than one face",
        7	=> "Wearing sunglasses	Đeo kính đen",
        8	=> "Wearing a hat	Đội mũ",
        9	=> "Wearing a mask	Đeo khẩu trang",
        10	=> "Photo taken from picture, screen, blurred noise or sign of fraud",
        11	=> "The face in the picture is too small",
        12	=> "The face in the portrait photo is too close to the margin",
    ],
    'mess_match_vi' => [
        0	=> "Thành công",
        1	=> "Ảnh đầu vào không có giấy tờ tùy thân",
        2	=> "Ảnh giấy tờ tùy thân là bản photocopy",
        3	=> "Ảnh giấy tờ tùy thân có dấu hiệu giả mạo",
        4	=> "Ảnh giấy tờ tùy thân không có mặt",
        5	=> "Ảnh chân dung không có mặt",
        6	=> "Ảnh chứa nhiều hơn một mặt người",
        7	=> "Đeo kính đen",
        8	=> "Đội mũ",
        9	=> "Đeo khẩu trang",
        10	=> "Ảnh chụp từ bức ảnh khác, màn hình thiết bị, bị mờ nhiễu hoặc có dấu hiệu gian lận",
        11	=> "Mặt người trong ảnh quá nhỏ",
        12	=> "Mặt người trong ảnh quá gần với lề",
    ],
    'KEY_API_EKYC' => "61cafa5c6a6148159935b07045ecfa7b",
    'SECRET_API_EKYC' => "e22cd40b5ac5268d05a0aafd5453552e53d5a4d596858057a34b0c0cd4fdd096",
    'URL_API_EKYC' =>"https://demo.computervision.com.vn",
    
];