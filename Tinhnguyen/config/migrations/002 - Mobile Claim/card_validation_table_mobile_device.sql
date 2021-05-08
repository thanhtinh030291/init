
-- --------------------------------------------------------

--
-- Table structure for table `mobile_device`
--

DROP TABLE IF EXISTS `mobile_device`;
CREATE TABLE `mobile_device` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mobile_user_id` char(36) NOT NULL,
  `device_token` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_device`
--

TRUNCATE TABLE `mobile_device`;
--
-- Triggers `mobile_device`
--
DROP TRIGGER IF EXISTS `mobile_device__id`;
DELIMITER $$
CREATE TRIGGER `mobile_device__id` BEFORE INSERT ON `mobile_device` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
