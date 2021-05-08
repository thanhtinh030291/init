
-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim_otp`
--

DROP TABLE IF EXISTS `mobile_claim_otp`;
CREATE TABLE `mobile_claim_otp` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` char(20) NOT NULL,
  `otp` char(6) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim_otp`
--

TRUNCATE TABLE `mobile_claim_otp`;
--
-- Triggers `mobile_claim_otp`
--
DROP TRIGGER IF EXISTS `mobile_claim_otp__id`;
DELIMITER $$
CREATE TRIGGER `mobile_claim_otp__id` BEFORE INSERT ON `mobile_claim_otp` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
