
-- --------------------------------------------------------

--
-- Table structure for table `mobile_user`
--

DROP TABLE IF EXISTS `mobile_user`;
CREATE TABLE `mobile_user` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `pocy_no` varchar(50) NOT NULL DEFAULT '',
  `mbr_no` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `address` varchar(200) NOT NULL,
  `photo` longtext DEFAULT NULL,
  `tel` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `language` enum('','_vi') NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user`
--

TRUNCATE TABLE `mobile_user`;
--
-- Triggers `mobile_user`
--
DROP TRIGGER IF EXISTS `mobile_user__id`;
DELIMITER $$
CREATE TRIGGER `mobile_user__id` BEFORE INSERT ON `mobile_user` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

alter table `card_validation`.`mobile_user` 
   add column `is_policy_holder` tinyint(1) NULL after `enabled`, 
   add column `member_type` tinyint(1) DEFAULT '1' NULL after `is_policy_holder`;