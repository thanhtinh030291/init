
-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim_status`
--

DROP TABLE IF EXISTS `mobile_claim_status`;
CREATE TABLE `mobile_claim_status` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `code` int(3) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `name_vi` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim_status`
--

TRUNCATE TABLE `mobile_claim_status`;
--
-- Dumping data for table `mobile_claim_status`
--

INSERT INTO `mobile_claim_status` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `code`, `name`, `name_vi`) VALUES
('fb01ff6b-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 10, 'New', 'Mới'),
('fb0377c2-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 11, 'Accepted', 'Chấp Nhận'),
('fb037b49-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 12, 'Partially Accepted', 'Chấp Nhận Một Phần'),
('fb03ea27-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 13, 'Declined', 'Từ Chối'),
('fb03ee1b-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 16, 'Info Request', 'Yêu Cầu Thông Tin'),
('c0b850b9-4ff7-11eb-ba33-000d3a821253', 'admin', '2021-01-06 08:19:24', NULL, NULL, 17, 'Info Submitted', 'Đã Nhận Thông Tin'),
('fb043d22-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 18, 'Ready For Process', 'Sẵn Sàng Xủ Lý');

--
-- Triggers `mobile_claim_status`
--
DROP TRIGGER IF EXISTS `mobile_claim_status__id`;
DELIMITER $$
CREATE TRIGGER `mobile_claim_status__id` BEFORE INSERT ON `mobile_claim_status` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
