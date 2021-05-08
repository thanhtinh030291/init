
-- --------------------------------------------------------

--
-- Table structure for table `lzalanguage`
--

DROP TABLE IF EXISTS `lzalanguage`;
CREATE TABLE `lzalanguage` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL,
  `order_by` int(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzalanguage`
--

TRUNCATE TABLE `lzalanguage`;
--
-- Dumping data for table `lzalanguage`
--

INSERT INTO `lzalanguage` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `code`, `order_by`) VALUES
('a3ef3d28-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'English', '', 1),
('a3ef6aec-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Tiếng Việt', '_vi', 2);

--
-- Triggers `lzalanguage`
--
DROP TRIGGER IF EXISTS `lzalanguage__id`;
DELIMITER $$
CREATE TRIGGER `lzalanguage__id` BEFORE INSERT ON `lzalanguage` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
