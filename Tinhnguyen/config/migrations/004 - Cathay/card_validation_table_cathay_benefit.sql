
-- --------------------------------------------------------

--
-- Table structure for table `cathay_benefit`
--

DROP TABLE IF EXISTS `cathay_benefit`;
CREATE TABLE `cathay_benefit` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `parent` char(36) DEFAULT NULL,
  `cathay_head_id` char(36) NOT NULL,
  `ben_type` varchar(10) NOT NULL,
  `ben_desc` varchar(500) NOT NULL,
  `ben_desc_vi` varchar(500) NOT NULL,
  `ben_note` varchar(500) NOT NULL,
  `ben_note_vi` varchar(500) NOT NULL,
  `is_combined` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_benefit`
--

TRUNCATE TABLE `cathay_benefit`;
--
-- Dumping data for table `cathay_benefit`
--

INSERT INTO `cathay_benefit` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `parent`, `cathay_head_id`, `ben_type`, `ben_desc`, `ben_desc_vi`, `ben_note`, `ben_note_vi`, `is_combined`) VALUES
('f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, 'f85e1b2d-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', 'Overall Maximum Limit Per Policy Year', 'Giới hạn tối đa cho 1 năm', 1),
('f860c52f-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e7607-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Outpatient Treatment (non-surgery)', 'Điều trị ngoại trú (không phẫu thuật)', 'fees for doctor, required diagnostic laboratory tests, imaging, prescribed medicines, medical supplies, and other related charges.<br />Co-pay 20:80 (Company pays 80%)', 'Bao gồm chi phí Bác sĩ, xét nghiệm chẩn đoán, chẩn đoán hình ảnh theo chỉ định của Bác sĩ, Thuốc được kê đơn, Vật tư y tế,  và các chi phí có liên quan khác. Đồng thanh toán 20:80 (Người được bảo hiểm tự trả 20% Chi phí hợp lý theo thông lệ).', NULL),
('f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e790c-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Outpatient Surgery (not Endoscopic surgery) Fee', 'Chi phí phẫu thuật ngoại trú (không bằng phương pháp nội soi)', 'fees for surgeon, operating room, anaesthetist, lab tests,  imaging, medical supplies, surgical appliances and devices, prescribed medicines, and other related charges.', 'Bao gồm chi phí bác sĩ phẫu thuật, chi phí phòng phẫu thuật, chi phí gây mê/gây tê, chi phí xét nghiệm, chẩn đoán hình ảnh, chi phí vật tư y tế, dụng cụ và trang thiết bị phẫu thuật, thuốc được kê đơn, và các chi phí có liên quan khác.', 1),
('f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e87b3-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Fee for Physiotherapy, Chiropractic in Outpatient Treatment when referred by Doctor', 'Chi phí vật lý trị liệu, trị liệu thần kinh cột sống trong điều trị ngoại trú  theo chỉ định của bác sĩ', 'maximum 30 days/year', 'tối đa 30 ngày/năm', 1),
('f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e6ca7-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Dental Benefit', 'Điều trị răng', 'Co-pay 20:80 (Company pays 80%)<br />Covers the costs of:<ul><li>Examination, X-rays</li><li>Treatment of gingivitis, pyorrhoea</li><li>Root tip resection, Removal of calculus under gum</li><li>Tooth filling</li><li>Root canal treatment</li><li>Extraction</li><li>Tooth cleaning (maximum 1 time/year)</ul>', 'Đồng thanh toán 20:80 (Người được bảo hiểm tự trả 20% Chi phí hợp lý theo thông lệ). Công ty sẽ chi trả 80% Chi phí hợp lý theo thông lệ cho các chi phí sau:<ul><li>Khám, chụp X quang răng bệnh lý</li><li>Điều trị viêm nướu, nha chu</li><li>Cắt chóp răng, lấy u vôi răng (lấy vôi răng sâu dưới nướu)</li><li>Trám răng bệnh lý</li><li>Điều trị tủy răng</li><li>Nhổ răng bệnh lý</li><li>Cạo vôi răng (tối đa 1 lần/năm)</li></ul>', 1);

--
-- Triggers `cathay_benefit`
--
DROP TRIGGER IF EXISTS `cathay_benefit__id`;
DELIMITER $$
CREATE TRIGGER `cathay_benefit__id` BEFORE INSERT ON `cathay_benefit` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
