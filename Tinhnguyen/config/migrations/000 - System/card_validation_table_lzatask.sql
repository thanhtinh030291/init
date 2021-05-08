
-- --------------------------------------------------------

--
-- Table structure for table `lzatask`
--

DROP TABLE IF EXISTS `lzatask`;
CREATE TABLE `lzatask` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL,
  `minute` varchar(255) NOT NULL,
  `hour` varchar(255) NOT NULL,
  `week_day` varchar(255) NOT NULL,
  `month_day` varchar(255) NOT NULL,
  `month` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `params` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzatask`
--

TRUNCATE TABLE `lzatask`;
--
-- Dumping data for table `lzatask`
--

INSERT INTO `lzatask` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `minute`, `hour`, `week_day`, `month_day`, `month`, `class`, `params`, `enabled`) VALUES
('885055bb-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Import PCV Data', '0', '*', '*', '*', '*', 'Lza\\App\\Task\\ImportDataTask', 'Pcv', 1),
('8851be10-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Import Fubon Data', '0', '*', '*', '*', '*', 'Lza\\App\\Task\\ImportDataTask', 'Fubon', 1),
('8851bef5-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Import Cathay Data', '0', '*', '*', '*', '*', 'Lza\\App\\Task\\ImportDataTask', 'Cathay', 1);

--
-- Triggers `lzatask`
--
DROP TRIGGER IF EXISTS `lzatask__id`;
DELIMITER $$
CREATE TRIGGER `lzatask__id` BEFORE INSERT ON `lzatask` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
