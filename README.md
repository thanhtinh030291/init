## Tài liêu hướng dẫn cài đặt 

## Yêu cầu 
 # Php version
 * 7.3 | 8.*

 # System dependencies 

* mysql 5.8
* nginx/1.15.8  or Apache2.4
* Larave FW 8.**


## Cài Đăt
 #Bước 1 : Checkout source code Từ SVN

 #Bước 2 : Set permissions on the project :
    ## sudo chown -R $USER:$USER

 #Bước 3: Set giá trị môi trường tại file .env
    ##cp .env.example .env
 
 #Bước 4: Database creation && đưa vào .env
        #DB_HOST .
        #DB_DATABASE .
        #DB_USERNAME .

 #Bước 5: Run load Thư viện và các lệnh hay sữ dụng
     php composer install     (Tại lại thư viện)
     php artisan key:generate (Khởi tạo khoá public cho app)
     php artisan storage:link (Ánh xa thư mục lưu trữ file)
     php artisan config:cache (xoá cache config constant)
     php artisan cache:clear  (xoá cache view ....)
     composer dump-autoload   (Chạy lại autoload composer)

 #Bước 6: Migrate && Seed
    php artisan migrate:fresh --seed
       :Do you wish to refresh migration before seeding, it will clear all old data ?  => No
       :Create Roles for user, default is admin and user?  => Yes
       :Enter roles in comma separate format.              => Admin,User
    note: 
        taikhoan: mặc định
                'name' => 'Administrator',
                'email' => admin@admin.com,
                'password' => 12345678,
 #Bước 7:  Khởi tạo chủ thể sở hữu WEB TOKEN API:
        php artisan passport:client --personal     => mobile
        
        [add value to file .env]
        PASSPORT_PERSONAL_ACCESS_CLIENT_ID="client-id-value"
        PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET="unhashed-client-secret-value"
 #Bước 8 : Run App
        php artisan serve  
        default (127.0.0.1:8000)

 #Bước 9 : Chạy cron Job nếu cần.
    crontab -e
        * * * * * php /var/www/html/mobile_backend/artisan schedule:run >> /dev/null 2>&1
    sudo service cron reload 

## Lấy Dữ liệu từ HBS
    php artisan command:GetMemberHbs   
        (Lấy toàn bộ thông tin user HBS ghi vào bảng hbs_member)
    php artisan command:GetBenefitMemberHbs  
        (Lấy benefit scheduler default của user ghi vào bảng hbs_member)

