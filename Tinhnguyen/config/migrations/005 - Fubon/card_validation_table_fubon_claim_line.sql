
-- --------------------------------------------------------

--
-- Table structure for table `fubon_claim_line`
--

DROP TABLE IF EXISTS `fubon_claim_line`;
CREATE TABLE `fubon_claim_line` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `cl_no` varchar(20) NOT NULL,
  `db_ref_no` varchar(30) DEFAULT NULL,
  `incur_date_from` date NOT NULL,
  `ben_head` varchar(20) NOT NULL,
  `ben_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `diag_code` varchar(20) DEFAULT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `prov_name` varchar(255) DEFAULT NULL,
  `pres_amt` double UNSIGNED DEFAULT NULL,
  `app_amt` double UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_claim_line`
--

TRUNCATE TABLE `fubon_claim_line`;
--
-- Triggers `fubon_claim_line`
--
DROP TRIGGER IF EXISTS `fubon_claim_line__id`;
DELIMITER $$
CREATE TRIGGER `fubon_claim_line__id` BEFORE INSERT ON `fubon_claim_line` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
