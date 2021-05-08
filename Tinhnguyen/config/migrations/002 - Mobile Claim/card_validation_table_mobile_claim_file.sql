
-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim_file`
--

DROP TABLE IF EXISTS `mobile_claim_file`;
CREATE TABLE `mobile_claim_file` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mobile_claim_id` char(36) NOT NULL,
  `note` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filetype` varchar(50) NOT NULL,
  `filesize` int(10) UNSIGNED NOT NULL,
  `checksum` char(32) NOT NULL,
  `contents` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim_file`
--

TRUNCATE TABLE `mobile_claim_file`;
--
-- Triggers `mobile_claim_file`
--
DROP TRIGGER IF EXISTS `mobile_claim_file__id`;
DELIMITER $$
CREATE TRIGGER `mobile_claim_file__id` BEFORE INSERT ON `mobile_claim_file` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
