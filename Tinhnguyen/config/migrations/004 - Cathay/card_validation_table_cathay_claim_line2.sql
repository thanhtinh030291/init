
-- --------------------------------------------------------

--
-- Table structure for table `cathay_claim_line2`
--

DROP TABLE IF EXISTS `cathay_claim_line2`;
CREATE TABLE `cathay_claim_line2` (
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
-- Truncate table before insert `cathay_claim_line2`
--

TRUNCATE TABLE `cathay_claim_line2`;
--
-- Triggers `cathay_claim_line2`
--
DROP TRIGGER IF EXISTS `cathay_claim_line2__id`;
DELIMITER $$
CREATE TRIGGER `cathay_claim_line2__id` BEFORE INSERT ON `cathay_claim_line2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
