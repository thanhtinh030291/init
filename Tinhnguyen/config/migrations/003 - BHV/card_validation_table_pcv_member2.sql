
-- --------------------------------------------------------

--
-- Table structure for table `pcv_member2`
--

DROP TABLE IF EXISTS `pcv_member2`;
CREATE TABLE `pcv_member2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `mbr_name_en` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('M','F') NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `mepl_oid` int(11) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `min_memb_eff_date` date NOT NULL,
  `min_pocy_eff_date` date NOT NULL,
  `insured_periods` varchar(500) NOT NULL,
  `wait_period` enum('Yes','No') NOT NULL,
  `spec_dis_period` enum('Yes','No') NOT NULL,
  `product` varchar(10) NOT NULL,
  `plan_desc` varchar(255) NOT NULL,
  `memb_rstr` text DEFAULT NULL,
  `memb_rstr_vi` text DEFAULT NULL,
  `primary_broker_name` varchar(255) NOT NULL,
  `broker_name` varchar(255) DEFAULT NULL,
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `ip_limit` double DEFAULT NULL,
  `op_copay_pct` int(3) UNSIGNED DEFAULT NULL,
  `op_limit_per_year` double DEFAULT NULL,
  `op_limit_per_visit` double DEFAULT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `dt_ind` enum('Yes','No') NOT NULL,
  `am_limit_per_year` double DEFAULT NULL,
  `os_limit_per_year` double DEFAULT NULL,
  `dt_limit_per_year` double DEFAULT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes',
  `ben_schedule` varchar(10000) DEFAULT NULL,
  `benefit_en` text DEFAULT NULL,
  `benefit_vi` text DEFAULT NULL,
  `children` varchar(4000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_member2`
--

TRUNCATE TABLE `pcv_member2`;
--
-- Triggers `pcv_member2`
--
DROP TRIGGER IF EXISTS `pcv_member2__id`;
DELIMITER $$
CREATE TRIGGER `pcv_member2__id` BEFORE INSERT ON `pcv_member2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

alter table `card_validation`.`pcv_member2` 
   add column `is_policy_holder` tinyint(1) NULL after `children`;