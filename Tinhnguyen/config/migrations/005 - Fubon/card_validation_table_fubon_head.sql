
-- --------------------------------------------------------

--
-- Table structure for table `fubon_head`
--

DROP TABLE IF EXISTS `fubon_head`;
CREATE TABLE `fubon_head` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `code` varchar(10) NOT NULL,
  `ben_heads` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_vi` varchar(500) NOT NULL,
  `fubon_benefit_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_head`
--

TRUNCATE TABLE `fubon_head`;
--
-- Dumping data for table `fubon_head`
--

INSERT INTO `fubon_head` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `code`, `ben_heads`, `name`, `name_vi`, `fubon_benefit_id`) VALUES
('df20f02a-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'OPALL', 'OP', 'OP Combined', 'Ngoại Trú Kết Hợp', 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1'),
('df227086-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'OV', 'OV', 'Office Visit', 'Phí Bác Sĩ', 'df2533d0-4a72-11eb-a7cf-98fa9b10d0b1'),
('df22859c-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'OVRX', 'OVRX', 'Diagnosis', 'Chi phí Chẩn đoán', 'df25383a-4a72-11eb-a7cf-98fa9b10d0b1'),
('df228b95-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'PHYS', 'PHYS', 'Physiotherapist', 'Chi phí vật lý trị liệu', 'df253cd6-4a72-11eb-a7cf-98fa9b10d0b1'),
('df228e30-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'TDAM', 'TDAM', 'Accidental Teeth Damage', 'Điều trị cấp cứu tổn thương răng do Tai nạn', 'df258731-4a72-11eb-a7cf-98fa9b10d0b1'),
('df22909a-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'DENT', 'DENT', 'General outpatient dental benefits', 'Quyền lợi cho răng ngoại trú tổng quát', 'df258a73-4a72-11eb-a7cf-98fa9b10d0b1');

--
-- Triggers `fubon_head`
--
DROP TRIGGER IF EXISTS `fubon_head__id`;
DELIMITER $$
CREATE TRIGGER `fubon_head__id` BEFORE INSERT ON `fubon_head` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
