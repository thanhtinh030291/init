
-- --------------------------------------------------------

--
-- Table structure for table `lzasession`
--

DROP TABLE IF EXISTS `lzasession`;
CREATE TABLE `lzasession` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `start` datetime NOT NULL,
  `access` datetime NOT NULL,
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzasession`
--

TRUNCATE TABLE `lzasession`;
--
-- Triggers `lzasession`
--
DROP TRIGGER IF EXISTS `lzasession__id`;
DELIMITER $$
CREATE TRIGGER `lzasession__id` BEFORE INSERT ON `lzasession` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
