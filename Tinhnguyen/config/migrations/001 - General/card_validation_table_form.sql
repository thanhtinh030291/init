
-- --------------------------------------------------------

--
-- Table structure for table `form`
--

DROP TABLE IF EXISTS `form`;
CREATE TABLE `form` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL,
  `name_vi` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `path_vi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `form`
--

TRUNCATE TABLE `form`;
--
-- Dumping data for table `form`
--

INSERT INTO `form` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `name_vi`, `path`, `path_vi`) VALUES
('9a4c852c-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Claim Form', 'Đơn Yêu Cầu Bồi Thường', 'resources/files/claim-form-en.pdf', 'resources/files/claim-form-vi.pdf'),
('9a4cc943-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'GOP Form', 'Đơn Yêu Cầu Bảo Lãnh Viện Phí', 'resources/files/gop-form.pdf', 'resources/files/gop-form.pdf'),
('9a4cd0d4-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Dental Examine Report', 'Báo Cáo Tổn Thương Nha Khoa', 'resources/files/dental-exam-report-en.pdf', 'resources/files/dental-exam-report-vi.pdf'),
('9a4d49fa-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Oral Examine Report', 'Báo Cáo Kiểm Tra Răng Miệng', 'resources/files/oral-exam-report-en.pdf', 'resources/files/oral-exam-report-vi.pdf'),
('9a4d4dea-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Incident Report', 'Báo Cáo Tai Nạn', 'resources/files/incident-report-en.pdf', 'resources/files/incident-report-vi.pdf'),
('9a4db724-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Progress Note', 'Phiếu  Theo Dõi Diễn Tiến Trị Liệu', 'resources/files/progress-note-en.pdf', 'resources/files/progress-note-vi.pdf'),
('9a4dba9d-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Treatment Plan', 'Kế Hoạch Điều Trị', 'resources/files/treatment-plan-en.pdf', 'resources/files/treatment-plan-vi.pdf');

--
-- Triggers `form`
--
DROP TRIGGER IF EXISTS `form__id`;
DELIMITER $$
CREATE TRIGGER `form__id` BEFORE INSERT ON `form` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
