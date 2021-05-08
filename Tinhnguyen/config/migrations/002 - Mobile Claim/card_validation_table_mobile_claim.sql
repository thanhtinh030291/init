
-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim`
--

DROP TABLE IF EXISTS `mobile_claim`;
CREATE TABLE `mobile_claim` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mantis_id` int(10) UNSIGNED NOT NULL,
  `mobile_user_id` char(36) NOT NULL,
  `pay_type` varchar(30) NOT NULL,
  `pres_amt` int(10) UNSIGNED NOT NULL,
  `mobile_user_bank_account_id` char(36) DEFAULT NULL,
  `mobile_claim_status_id` char(36) NOT NULL DEFAULT 'fb01ff6b-4a6b-11eb-a7cf-98fa9b10d0b1',
  `reason` varchar(30) DEFAULT NULL,
  `symtom_time` datetime DEFAULT NULL,
  `occur_time` datetime DEFAULT NULL,
  `body_part` varchar(300) DEFAULT NULL,
  `incident_detail` text DEFAULT NULL,
  `note` text NOT NULL,
  `dependent_memb_no` varchar(20) DEFAULT NULL,
  `fullname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim`
--

TRUNCATE TABLE `mobile_claim`;
--
-- Triggers `mobile_claim`
--
DROP TRIGGER IF EXISTS `mobile_claim__id`;
DELIMITER $$
CREATE TRIGGER `mobile_claim__id` BEFORE INSERT ON `mobile_claim` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
