
-- --------------------------------------------------------

--
-- Table structure for table `lzatext`
--

DROP TABLE IF EXISTS `lzatext`;
CREATE TABLE `lzatext` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `content_vi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzatext`
--

TRUNCATE TABLE `lzatext`;
--
-- Dumping data for table `lzatext`
--

INSERT INTO `lzatext` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `content`, `content_vi`) VALUES
('9fb33568-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Yes', 'Yes', 'Có'),
('9fb36bdc-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Deleted', 'Deleted', 'Đã xóa'),
('9fb36d18-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d/%m/%Y', '31/12/2000', '31/12/2000'),
('9fb36deb-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m/%d/%Y', '12/31/2000', '12/31/2000'),
('9fb36ee1-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y/%m/%d', '2000/12/31', '2000/12/31'),
('9fb36fb0-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d-%m-%Y', '31-12-2000', '31-12-2000'),
('9fb37065-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m-%d-%Y', '12-31-2000', '12-31-2000'),
('9fb37120-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y-%m-%d', '2000-12-31', '2000-12-31'),
('9fb371df-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d/%m/%Y %H:%i:%s', '31/12/2000 21:30:50', '31/12/2000 21:30:50'),
('9fb372a8-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m/%d/%Y %H:%i:%s', '12/31/2000 21:30:50', '12/31/2000 21:30:50'),
('9fb37372-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y/%m/%d %H:%i:%s', '2000/12/31 21:30:50', '2000/12/31 21:30:50'),
('9fb3742f-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'No', 'No', 'Không'),
('9fb374e3-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d-%m-%Y %H:%i:%s', '31-12-2000 21:30:50', '31-12-2000 21:30:50'),
('9fb375ad-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m-%d-%Y %H:%i:%s', '12-31-2000 21:30:50', '12-31-2000 21:30:50'),
('9fb37675-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y-%m-%d %H:%i:%s', '2000-12-31 21:30:50', '2000-12-31 21:30:50'),
('9fb37747-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'None', 'None', 'Không có'),
('9fb37802-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Administrator', 'Administrator', 'Quản trị viên'),
('9fb378b8-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'User', 'User', 'Người dùng'),
('9fb3797a-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Customer Service', 'Customer Service', 'Chăm sóc Khách hàng'),
('9fb37a44-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Underwriter', 'Underwriter', 'Thẩm định viên'),
('9fb37af8-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Created', 'Created', 'Đã tạo'),
('9fb37bb9-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Updated', 'Updated', 'Đã sửa');

--
-- Triggers `lzatext`
--
DROP TRIGGER IF EXISTS `lzatext__id`;
DELIMITER $$
CREATE TRIGGER `lzatext__id` BEFORE INSERT ON `lzatext` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
