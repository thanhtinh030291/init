
-- --------------------------------------------------------

--
-- Table structure for table `lzamodule`
--

DROP TABLE IF EXISTS `lzamodule`;
CREATE TABLE `lzamodule` (
  `id` char(50) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `db_id` varchar(20) NOT NULL DEFAULT 'main',
  `parent` varchar(50) DEFAULT NULL,
  `icon` varchar(50) NOT NULL,
  `single` varchar(50) NOT NULL,
  `plural` varchar(50) NOT NULL,
  `single_vi` varchar(50) NOT NULL,
  `plural_vi` varchar(50) NOT NULL,
  `note` varchar(500) NOT NULL,
  `note_vi` varchar(500) NOT NULL,
  `display` varchar(100) NOT NULL,
  `unique_keys` varchar(255) NOT NULL,
  `sort` varchar(100) NOT NULL,
  `enabled` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `settings` varchar(500) NOT NULL,
  `order_by` int(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzamodule`
--

TRUNCATE TABLE `lzamodule`;
--
-- Dumping data for table `lzamodule`
--

INSERT INTO `lzamodule` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `db_id`, `parent`, `icon`, `single`, `plural`, `single_vi`, `plural_vi`, `note`, `note_vi`, `display`, `unique_keys`, `sort`, `enabled`, `settings`, `order_by`) VALUES
('system', 'admin', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'lock', 'System', 'Systems', 'Hệ Thống', 'Hệ Thống', '', '', '', '', '', 'Yes', '', 0),
('lzarole', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'users', 'Role', 'Roles', 'Vai Trò', 'Vai Trò', 'Role', 'Vai Trò', 'name', '', '[1,\"asc\"]', 'Yes', '', 1),
('lzauser', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'user', 'User', 'Users', 'Ngươi Dùng', 'Ngươi Dùng', 'User', 'Ngươi Dùng', 'username', '', '[1,\"asc\"]', 'Yes', '', 2),
('lzafilter', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'filter', 'Filter', 'Filters', 'Bộ Lọc', 'Bộ Lọc', 'Filter', 'Bộ Lọc', 'name', '', '[1,\"asc\"]', 'Yes', '', 3),
('lzapermission', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'edit', 'Permission', 'Permissions', 'Quyền', 'Quyền', 'Permission', 'Quyền', 'id', '', '[1,\"asc\"]', 'Yes', '', 4),
('lzasession', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'refresh', 'Session', 'Sessions', 'Phiên', 'Phiên', 'Session', 'Phiên', 'start', '', '[1,\"asc\"]', 'Yes', '', 5),
('lzastatistic', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'bar-chart-o', 'Statistic', 'Statistics', 'Thống Kê', 'Thống Kê', 'Statistic', 'Thống Kê', 'name', '', '[1,\"asc\"]', 'Yes', '', 6),
('lzaapi', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'user', 'API Token', 'API Tokens', 'Mã API', 'Mã API', 'API Token', 'Mã API', 'username', '', '[1,\"asc\"]', 'Yes', '', 7),
('lzamodule', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'table', 'Module', 'Modules', 'lzamodule', 'lzamodule', 'Module', 'lzamodule', 'id', '', '[1,\"asc\"]', 'Yes', '', 8),
('lzaview', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'table', 'DB View', 'DB Views', 'Góc Nhìn Dữ Liệu', 'Góc Nhìn Dữ Liệu', 'DB View', 'Góc Nhìn Dữ Liệu', 'name', '', '[1,\"asc\"]', 'Yes', '', 9),
('lzafield', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'edit', 'Field', 'Fields', 'Mục', 'Mục', 'Field', 'Mục', 'single', '', '[1,\"asc\"]', 'Yes', '', 10),
('lzalanguage', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'language', 'Language', 'Languages', 'Ngôn Ngữ', 'Ngôn Ngữ', 'Language', 'Ngôn Ngữ', 'name', '', '[1,\"asc\"]', 'Yes', '', 11),
('lzatext', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'file-text', 'Text', 'Texts', 'Văn Bản', 'Văn Bản', 'Text', 'Văn Bản', 'name', '', '[1,\"asc\"]', 'Yes', '', 12),
('lzasection', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'gears', 'Section', 'Sections', 'Phần', 'Phần', 'Section', 'Phần', 'name', '', '[1,\"asc\"]', 'Yes', '', 13),
('lzasetting', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'gears', 'Setting', 'Settings', 'Cấu Hình', 'Cấu Hình', 'Setting', 'Cấu Hình', 'key', '', '[1,\"asc\"]', 'Yes', '', 14),
('lzatask', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'tasks', 'Task', 'Tasks', 'Tác Vụ', 'Tác Vụ', 'Task', 'Tác Vụ', 'name', '', '[1,\"asc\"]', 'Yes', '', 15),
('lzaemail', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'Email', 'Emails', 'Thư Điện Tử', 'Thư Điện Tử', 'Email', 'Thư Điện Tử', 'subject', '', '[1,\"asc\"]', 'Yes', '', 16),
('lzanotification', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'Notification', 'Notifications', 'Thông Báo', 'Thông Báo', 'Notification', 'Thông Báo', 'subject', '', '[1,\"asc\"]', 'Yes', '', 17),
('lzahttprequest', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'Http Request', 'Http Requests', 'Yêu Cầu Http', 'Yêu Cầu Http', 'Http Request', 'Yêu Cầu Http', 'url', '', '[1,\"asc\"]', 'Yes', '', 18),
('lzasms', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'SMS Message', 'SMS Messages', 'Tin Nhắn SMS', 'Tin Nhắn SMS', 'SMS Message', 'Tin Nhắn SMS', 'receiver', '', '[1,\"asc\"]', 'Yes', '', 19),
('user', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'user', 'User', 'Users', 'Người dùng', 'Người dùng', 'Store User Information to login and access admin panel', 'Chứa thông tin Người Dùng để đăng nhập và truy cập bảng Quản Trị', 'fullname', 'email,username', '[\"1\",\"asc\"]', 'Yes', '', 1),
('post', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'code', 'Post', 'Posts', 'Bài viết', 'Bài viết', 'Store Metadata and also content of front end web pages', 'Chứa Siêu Dữ Liệu và Nội dung của trang web', 'slug', 'slug', '[\"1\",\"asc\"]', 'Yes', '', 2),
('provider', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'user-md', 'Provider', 'Providers', 'Nhà cung cấp', 'Nhà cung cấp', 'Store Provider Ìnormation', 'Chứa thông tin Nhà cung cấp', 'name', 'name', '[\"1\",\"asc\"]', 'Yes', '', 3),
('form', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'file', 'Form', 'Forms', 'Đơn', 'Đơn', 'Store Form', 'Chứa đơn', 'filename', 'filename', '[\"1\",\"asc\"]', 'Yes', '', 4),
('pcv', 'admin', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'PCV', 'PCV', 'PCV', 'PCV', '', '', '', '', '', 'Yes', '', 5),
('pcv_benefit', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'file', 'PCV Benefit', 'PCV Benefits', 'Quyền Lợi PCV', 'Quyền Lợi PCV', 'Store PCV Benefits', 'Chứa Quyền Lợi PCV', 'ben_desc', 'ben_desc', '[\"1\",\"asc\"]', 'Yes', '', 1),
('pcv_head', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'file', 'PCV Benefit Head', 'PCV Benefit Heads', 'Loại Quyền Lợi PCV', 'Loại Quyền Lợi PCV', 'Store PCV Benefit Heads', 'Chứa Đầu Quyền', 'code', 'code', '[\"1\",\"asc\"]', 'Yes', '', 2),
('pcv_plan_desc_map', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'file', 'PCV Plan Desc Map', 'PCV Plan Desc Maps', 'Ánh Xạ Gói Bảo Hiểm PCV', 'Ánh Xạ Gói Bảo Hiểm PCV', 'Store PCV Plan Desc Maps', 'Chứa Bản Đồ Gói BH của PCV', 'haystack', 'haystack,needle', '[\"1\",\"asc\"]', 'Yes', '', 3),
('pcv_member', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'user', 'PCV Member', 'PCV Members', 'Thành viên PCV', 'Thành viên PCV', 'Store PCV Member Information', 'Chứa thông tin Thành Viên PCV', 'mbr_name', 'mbr_name', '[1,\"asc\"]', 'Yes', '', 4),
('pcv_claim_line', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'user', 'PCV Claim Line', 'PCV Claim Lines', 'Dòng Bồi thường PCV', 'Dòng Bồi thường PCV', 'Store PCV Claim Line Information', 'Chứa thông tin Dòng bồi thường của PCV', 'cl_no', 'cl_no', '[1,\"asc\"]', 'Yes', '', 5),
('pcv_history', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'history', 'PCV Check Card/Direct Billing History', 'PCV Check Card/Direct Billing Histories', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của PCV', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của PCV', 'Store PCV Check Card/Direct Billing Histories', 'Chứa Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của PCV', 'mbr_no', 'mbr_no', '[\"1\",\"asc\"]', 'Yes', '{\"history\": true}', 6),
('pcv_db_claim', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'history', 'PCV Direct Billing Claim', 'PCV Direct Billing Claims', 'Thanh Toán Trực Tiếp PCV', 'Bồi Thường Thanh Toán Trực Tiếp của PCV', 'Store PCV Direct Billing Claims', 'Chứa Bồi Thường Thanh Toán Trực Tiếp của PCV', 'db_ref_no', 'db_ref_no', '[\"1\",\"asc\"]', 'Yes', '', 7),
('fubon', 'admin', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'Fubon', 'Fubon', 'Fubon', 'Fubon', '', '', '', '', '', 'Yes', '', 6),
('fubon_benefit', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'file', 'Fubon Benefit', 'Fubon Benefits', 'Quyền Lợi Fubon', 'Quyền Lợi Fubon', 'Store Fubon Benefits', 'Chứa Quyền lợi Fubon', 'ben_desc', 'ben_desc', '[\"1\",\"asc\"]', 'Yes', '', 1),
('fubon_head', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'file', 'Fubon Benefit Head', 'Fubon Benefit Heads', 'Loại Quyền Lợi Fubon', 'Loại Quyền Lợi Fubon', 'Store Fubon Benefit Heads', 'Chứa Đầu Quyền lợi Fubon', 'code', 'code', '[\"1\",\"asc\"]', 'Yes', '', 2),
('fubon_member', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'user', 'Fubon Member', 'Fubon Members', 'Thành viên Fubon', 'Thành viên Fubon', 'Store Fubon Member Information', 'Chứa thông tin Thành Viên Fubon', 'mbr_name', 'mbr_name', '[1,\"asc\"]', 'Yes', '', 3),
('fubon_claim_line', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'user', 'Fubon Claim Line', 'Fubon Claim Lines', 'Dòng Bồi thường Fubon', 'Dòng Bồi thường Fubon', 'Store Fubon Claim Line Information', 'Chứa thông tin Dòng bồi thường của Fubon', 'cl_no', 'cl_no', '[1,\"asc\"]', 'Yes', '', 4),
('fubon_client', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'user', 'Fubon Client', 'Fubon Clients', 'Khách Hàng Fubon', 'Khách Hàng Fubon', 'Store Fubon Client Information', 'Chứa Thông tin Khách Hàng Fubon', 'mbr_name', 'poho_no,mbr_name,dob,gender', '[1,\"asc\"]', 'Yes', '', 5),
('fubon_history', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'history', 'Fubon Check Card/Direct Billing History', 'Fubon Check Card/Direct Billing Histories', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Fubon', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Fubon', 'Store Fubon Check Card/Direct Billing Histories', 'Chứa Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Fubon', 'mbr_no', 'mbr_no', '[\"1\",\"asc\"]', 'Yes', '{\"history\": true}', 6),
('fubon_db_claim', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'history', 'Fubon Direct Billing Claim', 'Fubon Direct Billing Claims', 'Thanh Toán Trực Tiếp Fubon', 'Bồi Thường Thanh Toán Trực Tiếp của Fubon', 'Store Fubon Direct Billing Claims', 'Chứa Bồi Thường Thanh Toán Trực Tiếp của Fubon', 'db_ref_no', 'db_ref_no', '[\"1\",\"asc\"]', 'Yes', '', 7),
('cathay', 'admin', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'Cathay', 'Cathay', 'Cathay', 'Cathay', '', '', '', '', '', 'Yes', '', 7),
('cathay_benefit', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'file', 'Cathay Benefit', 'Cathay Benefits', 'Quyền Lợi Cathay', 'Quyền Lợi Cathay', 'Store Cathay Benefits', 'Chứa Quyền lợi Cathay', 'ben_desc', 'ben_desc', '[\"1\",\"asc\"]', 'Yes', '', 1),
('cathay_head', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'file', 'Cathay Benefit Head', 'Cathay Benefit Heads', 'Loại Quyền Lợi Cathay', 'Loại Quyền Lợi Cathay', 'Store Cathay Benefit Heads', 'Chứa Đầu Quyền lợi Cathay', 'code', 'code', '[\"1\",\"asc\"]', 'Yes', '', 2),
('cathay_member', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'user', 'Cathay Member', 'Cathay Members', 'Thành viên Cathay', 'Thành viên Cathay', 'Store Cathay Member Information', 'Chứa thông tin Thành Viên Cathay', 'mbr_name', 'mbr_name', '[1,\"asc\"]', 'Yes', '', 3),
('cathay_claim_line', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'user', 'Cathay Claim Line', 'Cathay Claim Lines', 'Dòng Bồi thường Cathay', 'Dòng Bồi thường Cathay', 'Store Cathay Claim Line Information', 'Chứa thông tin Dòng bồi thường của Cathay', 'cl_no', 'cl_no', '[1,\"asc\"]', 'Yes', '', 4),
('cathay_history', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'history', 'Cathay Check Card/Direct Billing History', 'Cathay Check Card/Direct Billing Histories', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Cathay', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Cathay', 'Store Cathay Check Card/Direct Billing Histories', 'Chứa Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Cathay', 'mbr_no', 'mbr_no', '[\"1\",\"asc\"]', 'Yes', '{\"history\": true}', 5),
('cathay_db_claim', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'history', 'Cathay Direct Billing Claim', 'Cathay Direct Billing Claims', 'Thanh Toán Trực Tiếp Cathay', 'Bồi Thường Thanh Toán Trực Tiếp của Cathay', 'Store Cathay Direct Billing Claims', 'Chứa Bồi Thường Thanh Toán Trực Tiếp của Cathay', 'db_ref_no', 'db_ref_no', '[\"1\",\"asc\"]', 'Yes', '', 6),
('mobile', 'admin', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'Mobile', 'Mobiles', 'Di Động', 'Di Động', '', '', '', '', '', 'Yes', '', 8),
('mobile_user', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'user', 'Mobile User', 'Mobile Users', 'Người dùng Di Động', 'Người dùng Di Động', 'Store Mobile User Information to login', 'Chứa Thông tin Người dùng di động', 'fullname', 'fullname', '[\"1\",\"asc\"]', 'Yes', '', 1),
('mobile_device', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Device', 'Mobile Devices', 'Thiết Bị Di Động', 'Thiết Bị Di Động', 'Store Mobile Device Information', 'Chứa Thông Tin Thiết Bị Di động', 'name', 'name', '[\"1\",\"asc\"]', 'Yes', '', 2),
('mobile_user_bank_account', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'user', 'Mobile User Bank Account', 'Mobile User Bank Accounts', 'Tài khoản Ngân Hàng của Người dùng Di Động', 'Tài khoản Ngân Hàng của Người dùng Di Động', 'Store Mobile User Bank Accounts', 'Chứa Thông tin Tài Khoản của Người dùng di động', 'bank_acc_no', 'bank_acc_no', '[\"1\",\"asc\"]', 'Yes', '', 3),
('mobile_claim_status', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Claim Status', 'Mobile Claim Statuses', 'Trạng Thái của Bồi thường Di Động', 'Trạng Thái của Bồi thường Di Động', 'Store Mobile Claim Status Information', 'Chứa Thông Tin Trạng Thái Bồi Thường Di động', 'name', 'name', '[\"1\",\"asc\"]', 'Yes', '', 4),
('mobile_claim', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Claim', 'Mobile Claims', 'Bồi thường Di Động', 'Bồi thường Di Động', 'Store Mobile Claim Information', 'Chứa Thông Tin Bồi Thường Di động', 'id', 'id', '[\"1\",\"asc\"]', 'Yes', '', 5),
('mobile_claim_file', 'admin', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Claim File', 'Mobile Claim Files', 'Tập Tin Bồi Thường Di Động', 'Tập Tin Bồi Thường Di Động', 'Store Mobile Claim File Information', 'Chứa Thông Tin Tập Tin của Bồi Thường Di động', 'filename', 'filename', '[\"1\",\"asc\"]', 'Yes', '', 6);

--
-- Triggers `lzamodule`
--
DROP TRIGGER IF EXISTS `lzamodule__id`;
DELIMITER $$
CREATE TRIGGER `lzamodule__id` AFTER INSERT ON `lzamodule` FOR EACH ROW BEGIN SET @last_uuid = NEW.id; END
$$
DELIMITER ;
