
-- --------------------------------------------------------

--
-- Table structure for table `lzasection`
--

DROP TABLE IF EXISTS `lzasection`;
CREATE TABLE `lzasection` (
  `id` char(50) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `title` varchar(50) NOT NULL,
  `title_vi` varchar(50) NOT NULL,
  `order_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzasection`
--

TRUNCATE TABLE `lzasection`;
--
-- Dumping data for table `lzasection`
--

INSERT INTO `lzasection` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `title`, `title_vi`, `order_by`) VALUES
('setting_datetime', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Datetime', 'Ngày Giờ', 3),
('setting_information', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Information', 'Thông Tin', 4),
('setting_mobile_claim', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Mobile Claims', 'Bồi Thường Di Động', 5),
('setting_password', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Password', 'Mật khẩu', 2),
('setting_smtp', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Outgoing Email', 'Email ra', 1);

--
-- Triggers `lzasection`
--
DROP TRIGGER IF EXISTS `lzasection__id`;
DELIMITER $$
CREATE TRIGGER `lzasection__id` AFTER INSERT ON `lzasection` FOR EACH ROW BEGIN SET @last_uuid = NEW.id; END
$$
DELIMITER ;
