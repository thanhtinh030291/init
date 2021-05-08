# Cron job get HBS data
* Alter table "pcv_member2": 
~~~sql
alter table `card_validation2`.`pcv_member2` 
   add column `is_policy_holder` tinyint(1) NULL after `children`;
~~~
* Alter table "pcv_member":
~~~sql
alter table `card_validation`.`pcv_member` 
   add column `is_policy_holder` tinyint(1) NULL after `children`;
~~~
* Run cronjob test:
config\public\cron.php
```sh
PS D:\xampp\www\CoverageConfirmation2\trunk\config\public> php cron.php -force
```

# Notification

# Email

# User is policy holder
* Edit file: config\migrations\002 - Mobile Claim\card_validation_table_mobile_user.sql
~~~sql
alter table `card_validation`.`mobile_user` 
   add column `is_policy_holder` tinyint(1) NULL after `enabled`, 
   add column `member_type` tinyint(1) DEFAULT '1' NULL after `is_policy_holder`;
~~~
* Edit file: config\migrations\000 - System\card_validation_table_lzafield.sql
~~~sql
INSERT INTO `F` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzamodule_id`, `field`, `single`, `plural`, `single_vi`, `plural_vi`, `type`, `mandatory`, `is_unique`, `minlength`, `maxlength`, `regex`, `error`, `order_by`, `level`, `statistic`, `display`, `note`, `note_vi`) VALUES

('8f3b6a91-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'is_policy_holder', 'Is Policy Holder', 'Is Policy Holder', 'Là chủ hợp đồng', 'Là chủ hợp đồng', 'checkbox', 0, 0, 0, 1, '', '', 12, 14, '', '[\"Yes\",\"No\"]', 'Specify will this Mobile user is policy holder or not', 'Xác định Người dùng Di Động này có phải chủ hợp đồng hay không'),
('8f3b6a91-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'user_type', 'User Type', 'User Types', 'Loại người dùng', 'Loại người dùng', 'level', 0, 0, 0, 1, '', '', 13, 15, '', '{"Health":1, "Travel":2}', 'Specify will this Mobile user is policy holder or not', 'Xác định Người dùng Di Động này có phải chủ hợp đồng hay không'),

~~~
* Ref: field level app\admin\src\AdminRouter.class.php

```php
define('LIST_LEVEL', 1);
define('SHOW_LEVEL', 2);
define('ADD_LEVEL', 4);
define('EDIT_LEVEL', 8);
```

# Run composer
D:\xampp\www\CoverageConfirmation2\trunk\lib

Base
Client
ClientApi
ClientApiMember
ClientApiMemberPostLogin

# Task get data from HBS
app\task\src\PcvBenefitBuilder.class.php