
-- --------------------------------------------------------

--
-- Table structure for table `lzapermission`
--

DROP TABLE IF EXISTS `lzapermission`;
CREATE TABLE `lzapermission` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzarole_id` char(36) NOT NULL,
  `lzamodule_id` char(50) NOT NULL,
  `level` int(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzapermission`
--

TRUNCATE TABLE `lzapermission`;
--
-- Triggers `lzapermission`
--
DROP TRIGGER IF EXISTS `lzapermission__id`;
DELIMITER $$
CREATE TRIGGER `lzapermission__id` BEFORE INSERT ON `lzapermission` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
