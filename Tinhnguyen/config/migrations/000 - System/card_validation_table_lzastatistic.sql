
-- --------------------------------------------------------

--
-- Table structure for table `lzastatistic`
--

DROP TABLE IF EXISTS `lzastatistic`;
CREATE TABLE `lzastatistic` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzamodule_id` char(50) NOT NULL,
  `lzafield_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `conditions` varchar(500) NOT NULL,
  `extra` varchar(500) NOT NULL,
  `type` enum('Pie Chart','Horizontal Bar Chart','Vertical Bar Chart','Yearly Line Chart','Quarterly Line Chart','Monthly Line Chart','Weekly Line Chart','Daily Line Chart','Yearly Area Chart','Quarterly Area Chart','Monthly Area Chart','Weekly Area Chart','Daily Area Chart') NOT NULL,
  `width` enum('6','12') NOT NULL DEFAULT '12',
  `order_by` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzastatistic`
--

TRUNCATE TABLE `lzastatistic`;
--
-- Dumping data for table `lzastatistic`
--

INSERT INTO `lzastatistic` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzamodule_id`, `lzafield_id`, `user_id`, `name`, `conditions`, `extra`, `type`, `width`, `order_by`) VALUES
('c7d4a39a-4cee-11eb-bb4b-98fa9b10d0b1', NULL, '2021-01-02 11:36:35', NULL, NULL, 'user', '8f3c095b-4a64-11eb-a7cf-98fa9b10d0b1', '475a1daf-4bf3-11eb-8142-98fa9b10d0b1', 'User Role', '', '', 'Pie Chart', '6', 1);

--
-- Triggers `lzastatistic`
--
DROP TRIGGER IF EXISTS `lzastatistic__id`;
DELIMITER $$
CREATE TRIGGER `lzastatistic__id` BEFORE INSERT ON `lzastatistic` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
