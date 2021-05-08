
-- --------------------------------------------------------

--
-- Table structure for table `fubon_client2`
--

DROP TABLE IF EXISTS `fubon_client2`;
CREATE TABLE `fubon_client2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `poho_no` varchar(20) NOT NULL,
  `poho_name` varchar(255) NOT NULL,
  `tax_code` varchar(255) DEFAULT NULL,
  `pocy_type` varchar(255) NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pre_pocy_no` varchar(20) NOT NULL,
  `min_pocy_no` varchar(20) NOT NULL,
  `min_pocy_eff_date` datetime NOT NULL,
  `pocy_status` varchar(255) NOT NULL,
  `pocy_eff_date` datetime NOT NULL,
  `pocy_exp_date` datetime NOT NULL,
  `payment_mode` varchar(20) DEFAULT NULL,
  `prem_amt` varchar(20) NOT NULL,
  `mbr_name` varchar(255) NOT NULL,
  `memb_status` varchar(255) NOT NULL,
  `memb_eff_date` datetime NOT NULL,
  `memb_exp_date` datetime NOT NULL,
  `gender` varchar(10) NOT NULL,
  `dob` date NOT NULL,
  `id_card_no` varchar(20) NOT NULL,
  `citizen` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `plan` varchar(255) NOT NULL,
  `sum_insured` double NOT NULL,
  `ben_1` double DEFAULT NULL,
  `ben_2` double DEFAULT NULL,
  `ben_3` double DEFAULT NULL,
  `ben_4` double DEFAULT NULL,
  `ben_5` double DEFAULT NULL,
  `ben_6` double DEFAULT NULL,
  `exclusion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_client2`
--

TRUNCATE TABLE `fubon_client2`;
--
-- Triggers `fubon_client2`
--
DROP TRIGGER IF EXISTS `fubon_client2__id`;
DELIMITER $$
CREATE TRIGGER `fubon_client2__id` BEFORE INSERT ON `fubon_client2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
