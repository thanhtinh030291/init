
-- --------------------------------------------------------

--
-- Table structure for table `pcv_head`
--

DROP TABLE IF EXISTS `pcv_head`;
CREATE TABLE `pcv_head` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `pcv_benefit_id` char(36) NOT NULL,
  `code` varchar(10) NOT NULL,
  `ben_heads` varchar(100) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_vi` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_head`
--

TRUNCATE TABLE `pcv_head`;
--
-- Dumping data for table `pcv_head`
--

INSERT INTO `pcv_head` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `pcv_benefit_id`, `code`, `ben_heads`, `name`, `name_vi`) VALUES
('1ad45f76-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408579ff-4a6f-11eb-a7cf-98fa9b10d0b1', 'IPALL', 'IP', 'Inpatient Treatment', 'Điều trị Nội Trú'),
('1ad4a581-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'CMED', 'CMED', 'Chinese Prescribed Medicine', 'Chi phí Thuốc theo toa của Trung Quốc'),
('1ad4a908-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'HOV', 'HOV', 'Home doctor visit', 'Chi phí Thăm Khám tại nhà'),
('1ad4abe3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'HYNO', 'HYNO', 'Hypnotherapist', 'Chi phí Chuyên gia Thôi Miên'),
('1ad514d4-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'LAB', 'LAB', 'Laboratory Charges', 'Chi phí Xét nghiệm'),
('1ad517c3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISC', 'MISC', 'Miscellaneous Charges - covered', 'Chi phí y tế điều trị trong ngày được chi trả'),
('1ad51aed-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISN', 'MISN', 'Miscellaneous Charges - not cover', 'Chi phí y tế điều trị trong ngày không được chi trả'),
('1ad51d17-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'OV', 'OV', 'Office Visit', 'Phí bác sĩ'),
('1ad51f30-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'OVRX', 'OVRX', 'Consultation & medicine', 'Chi phí khám chữa bệnh'),
('1ad52142-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'PHYS', 'PHYS', 'Physiotherapist', 'Chi phí vật lý trị liệu'),
('1ad52404-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSYM', 'PSYM', 'Psychiatric & mental illnesses', 'Điều trị Bệnh Tâm Thần'),
('1ad52614-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'HVALL', 'PORX, POSH', 'Pre & Post Hospital Visit', 'Thăm khám trước & sau khi nhập viện'),
('1ad52828-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'RPFE', 'RPFE', 'Reprice Fee', 'Phí Thanh toán lại'),
('1ad52ae5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'RX', 'RX', 'Prescribed Medicine', 'Chi phí thuốc theo toa'),
('1ad52cef-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SP', 'SP', 'Specialist consultation', 'Chi phí Tư vấn Chuyên môn'),
('1ad52ef6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SPOR', 'SPOR', 'Special Sport Cover', 'Chi phí Bảo Hiểm cho Thể Thao Đặc Biệt'),
('1ad531b2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SUPP', 'SUPP', 'Supplies', 'Chi phí Vật Tư'),
('1ad552f6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SURAP', 'SURAP', 'Surgical appliances', 'Chi phí Dụng cụ phẫu thuật'),
('1ad5572f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'XRAY', 'XRAY', 'X-Ray', 'Chi phí chụp x-quang'),
('1ad559ee-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'AMALL', 'ACUP, BSET, CGP, HERB, HLIS, HMEO, OSTE', 'Alternative Medicines', 'Chi phí Y Học Thay Thế'),
('1ad55d6c-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'ACUP', 'ACUP', 'Acupuncture', 'Chi phí Châm cứu'),
('1ad5600e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'BSET', 'BSET', 'Bone Setter', 'Chi phí Nắn xương'),
('1ad5636e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'PORX', 'PORX', 'Pre Hospital Visit', 'Thăm khám trước khi nhập viện'),
('1ad566d4-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'CGP', 'CGP', 'Chinese Practitioner Consultation', 'Chi phí Tư Vấn Chuyên Gia Trung Quốc'),
('1ad56a3c-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'HERB', 'HERB', 'Prescribed herbs', 'Chi phí Thảo dược kê đơn'),
('1ad56d90-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'HLIS', 'HLIS', 'Herbalist', 'Chi phí Thảo dược'),
('1ad573fc-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'HMEO', 'HMEO', 'Homeopathic treatment', 'Điều trị vi lượng đồng căn'),
('1ad57643-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'OSTE', 'OSTE', 'Osteopathy', 'Điều trị Loãng xương'),
('1ad57878-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'MEDCALL', 'MEDC, VACI', 'Medical Checkup', 'Kiểm tra Y tế'),
('1ad57aaf-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'MEDC', 'MEDC', 'Medical Checkup', 'Kiểm tra Y tế'),
('1ad57ce0-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'VACI', 'VACI', 'Vaccine', 'Vắc Xin'),
('1ad57f20-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'MAT', 'MAT', 'Maternity', 'Khám thai'),
('1ad5814e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'DELIALL', 'DELI, MAT', 'Normal Delivery', 'Sinh thường'),
('1ad5837b-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'POSH', 'POSH', 'Post Hospital Visit', 'Thăm khám sau khi nhập viện'),
('1ad585a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'DELI', 'DELI', 'Normal Delivery', 'Sinh thường'),
('1ad587d1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'CXPALL', 'CXP, MAT', 'Surgical Delivery', 'Sinh Mổ'),
('1ad589f8-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'CXP', 'CXP', 'Surgical Delivery', 'Sinh Mổ'),
('1ad58c1d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MCARALL', 'MAT, MCAR', 'Miscarriage/Abortion', 'Sẩy/Bỏ Thai'),
('1ad58e46-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MCAR', 'MCAR', 'Miscarriage/Abortion', 'Sẩy/Bỏ Thai'),
('1ad5906f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DTALL', 'DT', 'Dental Treatment', 'Đều trị răng'),
('1ad59293-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ABS', 'ABS', 'Abscess w/o Surgery', 'Áp xe không phẫu thuật'),
('1ad594b6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ABSS', 'ABSS', 'Abscess with Surgery', 'Áp xe có phẫu thuật'),
('1ad596d9-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ADEN', 'ADEN', 'Treatment for dental accident', 'Điều trị tai biến nha khoa'),
('1ad59917-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'AE', 'AE', 'Anterior Teeth with Acid Etch', 'Răng trước có khắc axit'),
('1ad59b47-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4087639f-4a6f-11eb-a7cf-98fa9b10d0b1', 'IMIS', 'IMIS', 'Outpatient Surgery', 'Phẫu thuật ngoại trú'),
('1ad59d6b-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'APAT', 'APAT', 'Apicoetomy Anterior Teeth', 'Phẫu thuật nhổ bỏ răng trước'),
('1ad59f8d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'APMP', 'APMP', 'Apicoetomy Molar & Pre-Molar', 'Nhổ răng hàm và răng tiền hàm'),
('1ad5a1b6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'BR', 'BR', 'Bridge Per Unit', 'Bắc cầu trên mỗi đơn vị'),
('1ad5a3e2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'COMP', 'COMP', 'Anterior Teeth', 'Răng trước'),
('1ad5a66c-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'CR', 'CR', 'Crown Per Tooth', 'Niềng từng răng'),
('1ad5a8d5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'CSTI', 'CSTI', 'Complete Soft Tissue or Bony Impaction', 'Hoàn chỉnh Mô Mềm hoặc Tác động Xương'),
('1ad5ab4b-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DBUL', 'DBUL', 'Denture Upper & Lower', 'Hàm giả trên & dưới'),
('1ad5adb0-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DEPP', 'DEPP', 'Denture Partial Plate', 'Bộ phận răng giả'),
('1ad5b069-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DEPT', 'DEPT', 'Denture Partial Each Tooth', 'Làm từng chiếc răng giả'),
('1ad5b2e1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DENT', 'DENT', 'General out patient dental benefits', 'Quyền lợi điều trị nha khoa'),
('1ad5b553-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4087670f-4a6f-11eb-a7cf-98fa9b10d0b1', 'ER', 'ER', 'Emergency Room', 'Chi phí Phòng Cấp Cứu'),
('1ad5b783-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DUOL', 'DUOL', 'Denture Upper or Lower', 'Hàm giả trên hoặc dưới'),
('1ad5b9b7-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ETN', 'ETN', 'ER Normal Hr', 'ER Normal Hr'),
('1ad5bbe3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ETO', 'ETO', 'ER Outside Normal Hr', 'ER Outside Normal Hr'),
('1ad5be12-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'EXT', 'EXT', 'Extraction - Uncomplicated', 'Chiết xuất - Không phức tạp'),
('1ad5c0a1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'EXTI', 'EXTI', 'Extraction - Impacted Wisdom Teeth', 'Nhổ - Răng khôn bị ảnh hưởng'),
('1ad5c413-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GI1S', 'GI1S', 'Gold Inlay 1st Surface', 'Bề mặt thứ 1 dát vàng'),
('1ad5c7e2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GI2S', 'GI2S', 'Gold Inlay 2nd Surface', 'Bề mặt thứ 2 dát vàng'),
('1ad5cb5f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GI3S', 'GI3S', 'Gold Inlay 3rd Surface', 'Bề mặt thứ 3 dát vàng'),
('1ad5cf6d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR1', 'GPR1', '1st Gold Pin for Cusp Restoration', 'Chốt vàng thứ 1 để phục hồi múi'),
('1ad5d189-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR2', 'GPR2', '2nd Gold Pin for Cusp Restoration', 'Chốt vàng thứ 2 để phục hồi múi'),
('1ad5d3a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'OPALL', 'OP', 'Outpatient Treatment', 'Điều trị Ngoại Trú'),
('1ad5d5c5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR3', 'GPR3', '3rd Gold Pin for Cusp Restoration', 'Chốt vàng thứ 3 để phục hồi múi'),
('1ad5d7da-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR4', 'GPR4', '4th Gold Pin for Cusp Restoration', 'Chốt vàng thứ 4 để phục hồi múi'),
('1ad5d9f1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MF1S', 'MF1S', 'Molar & Pre-molar Filling 1st Surface', 'Làm đầy bề mặt răng hàm và răng tiền hàm trên thứ nhất'),
('1ad5dc17-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MF2S', 'MF2S', 'Molar & Pre-molar Filling 2nd Surface', 'Làm đầy bề mặt răng hàm và răng tiền hàm trên thứ 2'),
('1ad5de3e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MFPT', 'MFPT', 'Molar Filling Per Tooth', 'Làm đầy răng hàm trên mỗi răng'),
('1ad5e058-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISC', 'MISC', 'Covered misc charges', 'Các khoản phí khác được bảo hiểm'),
('1ad5e26d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISN', 'MISN', 'Charges is not covered', 'Các khoản phí không được bảo hiểm'),
('1ad5e484-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'OE1', 'OE1', 'Oral Examination', 'Quyền lợi kiểm tra răng miệng 1'),
('1ad6004a-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'OE2', 'OE2', 'Oral Examination', 'Quyền lợi kiểm tra răng miệng 2'),
('1ad60441-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ORTH', 'ORTH', 'Orthodontic Treatment Per Year', 'Điều trị chỉnh nha mỗi năm'),
('1ad606de-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'CHEMO', 'CHEMO', 'Oncology (chemotherapy)', 'Điều trị Ung thư (hóa trị liệu)'),
('1ad6094a-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'OR17', 'OR17', 'Orthodontic treatment : children up to 17', 'Điều trị chỉnh nha: trẻ em đến 17 tuổi'),
('1ad60ba6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSGQ', 'PSGQ', 'Periodontal Gingivectomy Per Quadrant (include Post OP Visit)', 'Cắt nướu nha chu cho mỗi phần tư (bao gồm Tái khám cho bệnh nhân ngoại trú)'),
('1ad60e40-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS1T', 'PS1T', '1st Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 1'),
('1ad6107d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS2T', 'PS2T', '2nd Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 2'),
('1ad612ab-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS3T', 'PS3T', '3rd Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 3'),
('1ad614d2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS4T', 'PS4T', '4th Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 4'),
('1ad616fa-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS5T', 'PS5T', '5th Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 5'),
('1ad61915-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS6T', 'PS6T', '6th Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 6'),
('1ad61b4f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSSC', 'PSSC', 'Periodontal Subgingival Curretage Per Treatment', 'Nạo răng dưới nướu cho mỗi lần điều trị'),
('1ad61d76-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSTI', 'PSTI', 'Partial Soft Tissue Impaction', 'Lực ép một phần lên mô mềm'),
('1ad62087-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'CHIR', 'CHIR', 'Chiropractor', 'Điều trị bệnh về Chân'),
('1ad622b1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF1', 'RCF1', '1st Root Canal', 'Ống tủy thứ 1'),
('1ad62505-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF2', 'RCF2', '2nd Root Canal', 'Ống tủy thứ 2'),
('1ad62723-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF3', 'RCF3', '3rd Root Canal', 'Ống tủy thứ 3'),
('1ad62959-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF4', 'RCF4', '4th Root Canal', 'Ống tủy thứ 4'),
('1ad62b78-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'XR1', 'XR1', '1ST X-Ray', 'X-Ray lần 1'),
('1ad62d9d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'XR2', 'XR2', 'Each Additional Film', 'Mỗi phim bổ sung'),
('1ad62fbb-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'XRPA', 'XRPA', 'Panoramic', 'Chụp toàn hàm');

--
-- Triggers `pcv_head`
--
DROP TRIGGER IF EXISTS `pcv_head__id`;
DELIMITER $$
CREATE TRIGGER `pcv_head__id` BEFORE INSERT ON `pcv_head` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
