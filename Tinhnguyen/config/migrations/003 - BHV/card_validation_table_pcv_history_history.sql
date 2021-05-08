
-- --------------------------------------------------------

--
-- Table structure for table `pcv_history_history`
--

DROP TABLE IF EXISTS `pcv_history_history`;
CREATE TABLE `pcv_history_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `mantis_id` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(30) DEFAULT NULL,
  `time` datetime(6) NOT NULL,
  `pocy_no` varchar(50) DEFAULT NULL,
  `mbr_no` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `provider_id` char(36) DEFAULT NULL,
  `incur_date` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_history_history`
--

TRUNCATE TABLE `pcv_history_history`;