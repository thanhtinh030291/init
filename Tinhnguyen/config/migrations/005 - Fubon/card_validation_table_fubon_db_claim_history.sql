
-- --------------------------------------------------------

--
-- Table structure for table `fubon_db_claim_history`
--

DROP TABLE IF EXISTS `fubon_db_claim_history`;
CREATE TABLE `fubon_db_claim_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `db_ref_no` varchar(50) DEFAULT NULL,
  `fubon_history_id` char(36) DEFAULT NULL,
  `fubon_head_id` char(36) DEFAULT NULL,
  `pres_amt` int(10) UNSIGNED DEFAULT NULL,
  `app_amt` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Confirmed','Canceled','Deleted','Accepted','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_db_claim_history`
--

TRUNCATE TABLE `fubon_db_claim_history`;