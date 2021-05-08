
-- --------------------------------------------------------

--
-- Table structure for table `pcv_plan_desc_map`
--

DROP TABLE IF EXISTS `pcv_plan_desc_map`;
CREATE TABLE `pcv_plan_desc_map` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `crt_by` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `haystack` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `needle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_by` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `pcv_plan_desc_map`
--

TRUNCATE TABLE `pcv_plan_desc_map`;
--
-- Dumping data for table `pcv_plan_desc_map`
--

INSERT INTO `pcv_plan_desc_map` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `haystack`, `needle`, `order_by`) VALUES
('665d3f00-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EM1', 'EMERGENCY 1', 1),
('665d56ff-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EM2', 'EMERGENCY 2', 2),
('665d5c09-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EM3', 'EMERGENCY 3', 3),
('665d6fad-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'M1', 'MASTER M1+', 4),
('665e184a-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'M2', 'MASTER M2', 5),
('665e1ba3-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'M3', 'MASTER M3', 6),
('665e1e12-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'SMASTER', 'SENIOR', 7),
('665e206b-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'SENIOR M1+', 'SENIOR M1', 8),
('665e22bd-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'FS (STD)', 'FOUNDATION (STANDARD)', 9),
('665e2516-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'FS (EXE)', 'FOUNDATION (EXECUTIVE)', 10),
('665e275a-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'FS (PRM)', 'FOUNDATION (PREMIER)', 11),
('665e2992-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'OP (STD)', 'OUTPATIENT (STANDARD)', 12),
('665e2bcc-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'OP (EXE)', 'OUTPATIENT (EXECUTIVE)', 13),
('665e2e06-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'OP (PRM)', 'OUTPATIENT (PREMIER)', 14),
('665e3040-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' OP', ' OUTPATIENT', 15),
('665e3278-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'STD,', 'STANDARD,', 16),
('665e34b1-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EXE,', 'EXECUTIVE,', 17),
('665e370d-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PRM,', 'PREMIER,', 18),
('665e3947-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'TK', ' TAKE-OVER', 19),
('665e3b80-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'WO/', 'WITHOUT ', 20),
('665e3db9-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/ O', 'W/O', 21),
('665e3ff2-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O OP', 'WITHOUT OUTPATIENT', 22),
('665e4227-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O DT', 'WITHOUT DENTAL', 23),
('665e445c-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O PA', 'WITHOUT PERSONAL ACCIDENT', 24),
('665e4694-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O ', 'WITHOUT ', 25),
('665e48ca-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'DT1', 'DENTAL 1', 26),
('665e4b02-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'DT2', 'DENTAL 2', 27),
('665e4d36-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'DT3', 'DENTAL 3', 28),
('665e4f6c-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' DT', ' DENTAL', 29),
('665e51a6-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PA1', 'PERSONAL ACCIDENT 1', 30),
('665e53e1-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PA2', 'PERSONAL ACCIDENT 2', 31),
('665e561a-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PA3', 'PERSONAL ACCIDENT 3', 32),
('665e5856-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' PA', ' PERSONAL ACCIDENT', 33),
('665e5a8e-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' CO-PAY', ' CO-PAYMENT', 34),
('665e5ce2-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' DED', ' DEDUCTIBLE', 35),
('665e5f1b-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' TR', ' TRAVEL', 36),
('665e614f-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' RB', ' ROOM & BOARD', 37),
('665e6388-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' TAL', ' TREATMENT AREA LIMIT', 38),
('665e65c2-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' SUR', ' SURGERY', 39);

--
-- Triggers `pcv_plan_desc_map`
--
DROP TRIGGER IF EXISTS `pcv_plan_desc_map__id`;
DELIMITER $$
CREATE TRIGGER `pcv_plan_desc_map__id` BEFORE INSERT ON `pcv_plan_desc_map` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
