
-- --------------------------------------------------------

--
-- Table structure for table `pcv_benefit`
--

DROP TABLE IF EXISTS `pcv_benefit`;
CREATE TABLE `pcv_benefit` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `parent` char(36) DEFAULT NULL,
  `pcv_head_id` char(36) NOT NULL,
  `ben_type` varchar(10) NOT NULL,
  `ben_desc` varchar(500) NOT NULL,
  `ben_desc_vi` varchar(500) NOT NULL,
  `ben_note` varchar(500) NOT NULL,
  `ben_note_vi` varchar(500) NOT NULL,
  `gender` enum('M','F','B') NOT NULL,
  `is_combined` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_gop` tinyint(1) UNSIGNED DEFAULT NULL,
  `no_first_year` enum('Y','N') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_benefit`
--

TRUNCATE TABLE `pcv_benefit`;
--
-- Dumping data for table `pcv_benefit`
--

INSERT INTO `pcv_benefit` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `parent`, `pcv_head_id`, `ben_type`, `ben_desc`, `ben_desc_vi`, `ben_note`, `ben_note_vi`, `gender`, `is_combined`, `is_gop`, `no_first_year`) VALUES
('408579ff-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad45f76-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'In-patient Treatment', 'Điều trị nội trú', '', '', 'B', 1, 0, 'Y'),
('4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad58c1d-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Mandatory miscarriage or abortion as prescribed by doctor', 'Sảy thai hoặc phá thai bắt buộc theo chỉ định của bác sĩ', '(90 days waiting)', '(90 ngày chờ)', 'F', 1, 1, 'Y'),
('4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad5906f-4a6f-11eb-a7cf-98fa9b10d0b1', 'DT', 'Dental Treatment', 'Điều trị răng', '(co-payment 80-20)', '(đồng thanh toán 80-20)', 'B', 1, 1, 'Y'),
('4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad52614-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'Pre & Post Hospital Visit', 'Thăm khám trước & sau khi nhập viện', '', '', 'B', 1, 1, 'Y'),
('4087639f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad59b47-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'Outpatient Surgery', 'Phẫu thuật ngoại trú', '', '', 'B', 1, 1, 'Y'),
('4087670f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad5b553-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'Emergency due to Accident', 'Cấp cứu do tai nạn', '', '', 'B', 1, 1, 'Y'),
('408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad5d3a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', '', '', 'B', 1, 0, 'Y'),
('40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad5d3a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', '', '', 'B', 1, 1, 'Y'),
('40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad559ee-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Alternative Medicines', 'Y học thay thế', '', '', 'B', 1, 1, 'Y'),
('40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad57878-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Medical Checkup', 'Khám tổng quát định kỳ hàng năm', '', '', 'B', 1, 0, 'Y'),
('408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad5814e-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Maternity', 'Quyền lợi thai sản', '(including antenatal care & pregnancy related drugs, GOP is only applied after 12-month waiting period)', '(bao gồm khám thai & thuốc liên quan đến thai sản, chỉ áp dụng bảo lãnh sau 12 tháng chờ)', 'F', 1, 1, 'Y');

--
-- Triggers `pcv_benefit`
--
DROP TRIGGER IF EXISTS `pcv_benefit__id`;
DELIMITER $$
CREATE TRIGGER `pcv_benefit__id` BEFORE INSERT ON `pcv_benefit` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
