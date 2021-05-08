
-- --------------------------------------------------------

--
-- Table structure for table `lzarole`
--

DROP TABLE IF EXISTS `lzarole`;
CREATE TABLE `lzarole` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(50) NOT NULL,
  `name_vi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzarole`
--

TRUNCATE TABLE `lzarole`;
--
-- Dumping data for table `lzarole`
--

INSERT INTO `lzarole` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `name_vi`) VALUES
('2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'User', 'Người Dùng'),
('2e6887d6-4a66-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'New Business', 'Thẩm Định'),
('2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Customer Service', 'Dịch Vụ Khách Hàng');

--
-- Triggers `lzarole`
--
DROP TRIGGER IF EXISTS `lzarole__id`;
DELIMITER $$
CREATE TRIGGER `lzarole__id` BEFORE INSERT ON `lzarole` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
