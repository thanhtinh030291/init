
-- --------------------------------------------------------

--
-- Table structure for table `mobile_user_session`
--

DROP TABLE IF EXISTS `mobile_user_session`;
CREATE TABLE `mobile_user_session` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` char(20) NOT NULL,
  `token` char(50) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user_session`
--

TRUNCATE TABLE `mobile_user_session`;
--
-- Triggers `mobile_user_session`
--
DROP TRIGGER IF EXISTS `mobile_user_session__id`;
DELIMITER $$
CREATE TRIGGER `mobile_user_session__id` BEFORE INSERT ON `mobile_user_session` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
