
-- --------------------------------------------------------

--
-- Table structure for table `fubon_benefit`
--

DROP TABLE IF EXISTS `fubon_benefit`;
CREATE TABLE `fubon_benefit` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `parent` char(36) DEFAULT NULL,
  `ben_type` varchar(10) NOT NULL,
  `ben_desc` varchar(500) NOT NULL,
  `ben_desc_vi` varchar(500) NOT NULL,
  `ben_note` varchar(500) NOT NULL,
  `ben_note_vi` varchar(500) NOT NULL,
  `fubon_head_id` char(36) NOT NULL,
  `is_combined` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_benefit`
--

TRUNCATE TABLE `fubon_benefit`;
--
-- Dumping data for table `fubon_benefit`
--

INSERT INTO `fubon_benefit` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `parent`, `ben_type`, `ben_desc`, `ben_desc_vi`, `ben_note`, `ben_note_vi`, `fubon_head_id`, `is_combined`) VALUES
('df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', 'Overall Maximum Limit Per Policy Year', 'Giới hạn tối đa cho 1 năm', 'df20f02a-4a72-11eb-a7cf-98fa9b10d0b1', 1),
('df2533d0-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Consultation fees', 'Chi phí khám', '(Primary and Specialist Care)', '(Khám Tổng quát và Chuyên sâu)', 'df227086-4a72-11eb-a7cf-98fa9b10d0b1', NULL),
('df25383a-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Miscellaneous charges', 'Chi phí y tế khác', '(Relating to Primary and Specialist Care)<br /> * Diagnostic procedures (blood test,x-ray,…)<br /> * Drugs and dressings', '(Điều trị Tổng quát và Chuyên sâu)<br /> * Chi phí chẩn đoán (xét nghiệm máu,chụp x-ray,…)<br /> * Thuốc và vật dụng băng bó', 'df22859c-4a72-11eb-a7cf-98fa9b10d0b1', 1),
('df253cd6-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Alternative Treatment', 'Điều trị hỗ trợ', '(Consultation fees and treatment provided and prescribed by a qualified and registered chiropractor,podiatrist,dietitian,naturopath,acupuncturist,homeopath,osteopath,physiotherapist and traditional Chinese medicine practitioner)', '(Khám và điều trị được bác sĩ chỉ định  và được thực hiện bởi chuyên viên châm cứu,trị liệu cột sống,chuyên viên dinh dưỡng,trị liệu bằng phương pháp vi lượng đồng căn, trị liệu thiên nhiên,nắn xương khớp,vật lí trị liệu và Y học Trung Hoa có giấy phép hành nghề hợp pháp)', 'df228b95-4a72-11eb-a7cf-98fa9b10d0b1', NULL),
('df258731-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Accidental Damage to Natural Teeth', 'Điều trị răng bị tổn thương do tai nạn', '', '', 'df228e30-4a72-11eb-a7cf-98fa9b10d0b1', NULL),
('df258a73-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Routine and Preventive Dental Care', 'Chăm sóc và điều trị răng', '', '', 'df22909a-4a72-11eb-a7cf-98fa9b10d0b1', NULL);

--
-- Triggers `fubon_benefit`
--
DROP TRIGGER IF EXISTS `fubon_benefit__id`;
DELIMITER $$
CREATE TRIGGER `fubon_benefit__id` BEFORE INSERT ON `fubon_benefit` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
