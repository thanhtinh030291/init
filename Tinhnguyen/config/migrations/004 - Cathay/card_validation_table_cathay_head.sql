
-- --------------------------------------------------------

--
-- Table structure for table `cathay_head`
--

DROP TABLE IF EXISTS `cathay_head`;
CREATE TABLE `cathay_head` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `cathay_benefit_id` char(36) NOT NULL,
  `code` varchar(10) NOT NULL,
  `ben_heads` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_vi` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_head`
--

TRUNCATE TABLE `cathay_head`;
--
-- Dumping data for table `cathay_head`
--

INSERT INTO `cathay_head` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `cathay_benefit_id`, `code`, `ben_heads`, `name`, `name_vi`) VALUES
('f85e1b2d-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'OPALL', 'OP', 'OP Combined', 'Ngoại Trú Kết Hợp'),
('f85e68bc-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'CHIR', 'CHIR', 'Chiropractic', 'Phí Trị Liệu Thần Kinh Cột Sống'),
('f85e6ca7-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'DTALL', 'DENT,TCL', 'Dental Combined', 'Điều trị Nha Khoa Kết Hợp'),
('f85e6fe6-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'DENT', 'DENT', 'General Outpatient Dental Benefits', 'Phí Điều Trị Răng Tổng Quát'),
('f85e72f8-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'TCL', 'TCL', 'Toot Cleaning', 'Cạo Vôi Răng'),
('f85e7607-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c52f-4a71-11eb-a7cf-98fa9b10d0b1', 'OV', 'OV', 'Office Visit', 'Phí Bác Sĩ'),
('f85e790c-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'SURALL', 'SUR, OPR, ANES, OMIS', 'Surgery Combined', 'Phí Phẫu Thuật Kết Hợp'),
('f85e7c05-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'SUR', 'SUR', 'Surgery', 'Phí Bác Sĩ Phẫu Thuật'),
('f85e7eed-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'OPR', 'OPR', 'Operating Room', 'Phí Phòng Phẫu Thuật'),
('f85e81f2-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'ANES', 'ANES', 'Anaesthetist', 'Phí Gây Mê/Tê'),
('f85e84c7-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'OMIS', 'OMIS', 'Medical Supplies', 'Phí Vật Tư Y Tế'),
('f85e87b3-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'PHYSALL', 'PHYS,CHIR', 'Physiotherapy Combined', 'Phí Vật Lý Trị Liệu Kết Hợp'),
('f85e8a66-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'PHYS', 'PHYS', 'Physiotherapy', 'Phí Vật Lý Trị Liệu');

--
-- Triggers `cathay_head`
--
DROP TRIGGER IF EXISTS `cathay_head__id`;
DELIMITER $$
CREATE TRIGGER `cathay_head__id` BEFORE INSERT ON `cathay_head` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
