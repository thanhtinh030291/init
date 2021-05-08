
-- --------------------------------------------------------

--
-- Table structure for table `fubon_client`
--

DROP TABLE IF EXISTS `fubon_client`;
CREATE TABLE `fubon_client` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `poho_no` varchar(20) DEFAULT NULL,
  `poho_name` varchar(255) DEFAULT NULL,
  `tax_code` varchar(255) DEFAULT NULL,
  `pocy_type` varchar(255) DEFAULT NULL,
  `pocy_no` varchar(20) DEFAULT NULL,
  `pre_pocy_no` varchar(20) DEFAULT NULL,
  `min_pocy_no` varchar(20) DEFAULT NULL,
  `min_pocy_eff_date` datetime DEFAULT NULL,
  `pocy_status` varchar(255) DEFAULT NULL,
  `pocy_eff_date` datetime DEFAULT NULL,
  `pocy_exp_date` datetime DEFAULT NULL,
  `payment_mode` varchar(20) DEFAULT NULL,
  `prem_amt` varchar(20) DEFAULT NULL,
  `mbr_name` varchar(255) DEFAULT NULL,
  `memb_status` varchar(255) DEFAULT NULL,
  `memb_eff_date` datetime DEFAULT NULL,
  `memb_exp_date` datetime DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `id_card_no` varchar(20) DEFAULT NULL,
  `citizen` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `plan` varchar(255) DEFAULT NULL,
  `sum_insured` double DEFAULT NULL,
  `ben_1` double DEFAULT NULL,
  `ben_2` double DEFAULT NULL,
  `ben_3` double DEFAULT NULL,
  `ben_4` double DEFAULT NULL,
  `ben_5` double DEFAULT NULL,
  `ben_6` double DEFAULT NULL,
  `exclusion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_client`
--

TRUNCATE TABLE `fubon_client`;
--
-- Triggers `fubon_client`
--
DROP TRIGGER IF EXISTS `fubon_client__id`;
DELIMITER $$
CREATE TRIGGER `fubon_client__id` BEFORE INSERT ON `fubon_client` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
