
-- --------------------------------------------------------

--
-- Table structure for table `lzaview`
--

DROP TABLE IF EXISTS `lzaview`;
CREATE TABLE `lzaview` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzamodule_id` char(50) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzaview`
--

TRUNCATE TABLE `lzaview`;
--
-- Triggers `lzaview`
--
DROP TRIGGER IF EXISTS `lzaview__id`;
DELIMITER $$
CREATE TRIGGER `lzaview__id` BEFORE INSERT ON `lzaview` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
