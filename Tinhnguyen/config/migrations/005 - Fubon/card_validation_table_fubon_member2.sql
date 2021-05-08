
-- --------------------------------------------------------

--
-- Table structure for table `fubon_member2`
--

DROP TABLE IF EXISTS `fubon_member2`;
CREATE TABLE `fubon_member2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `mbr_name_2` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
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
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_member2`
--

TRUNCATE TABLE `fubon_member2`;
--
-- Triggers `fubon_member2`
--
DROP TRIGGER IF EXISTS `fubon_member2__id`;
DELIMITER $$
CREATE TRIGGER `fubon_member2__id` BEFORE INSERT ON `fubon_member2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
