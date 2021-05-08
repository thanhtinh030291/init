
-- --------------------------------------------------------

--
-- Table structure for table `mobile_user_reset_password`
--

DROP TABLE IF EXISTS `mobile_user_reset_password`;
CREATE TABLE `mobile_user_reset_password` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(200) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user_reset_password`
--

TRUNCATE TABLE `mobile_user_reset_password`;
--
-- Triggers `mobile_user_reset_password`
--
DROP TRIGGER IF EXISTS `mobile_user_reset_password__id`;
DELIMITER $$
CREATE TRIGGER `mobile_user_reset_password__id` BEFORE INSERT ON `mobile_user_reset_password` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
