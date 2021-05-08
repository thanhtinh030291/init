
-- --------------------------------------------------------

--
-- Table structure for table `mobile_user_bank_account`
--

DROP TABLE IF EXISTS `mobile_user_bank_account`;
CREATE TABLE `mobile_user_bank_account` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mobile_user_id` char(36) NOT NULL,
  `bank_name` varchar(250) NOT NULL,
  `bank_address` varchar(250) NOT NULL,
  `bank_acc_no` varchar(50) NOT NULL,
  `bank_acc_name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user_bank_account`
--

TRUNCATE TABLE `mobile_user_bank_account`;
--
-- Triggers `mobile_user_bank_account`
--
DROP TRIGGER IF EXISTS `mobile_user_bank_account__id`;
DELIMITER $$
CREATE TRIGGER `mobile_user_bank_account__id` BEFORE INSERT ON `mobile_user_bank_account` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
