
-- --------------------------------------------------------

--
-- Table structure for table `lzanotification`
--

DROP TABLE IF EXISTS `lzanotification`;
CREATE TABLE `lzanotification` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('device','topic','group') NOT NULL,
  `receivers` text NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` varchar(500) NOT NULL,
  `data` varchar(500) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `try` tinyint(3) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzanotification`
--

TRUNCATE TABLE `lzanotification`;
--
-- Triggers `lzanotification`
--
DROP TRIGGER IF EXISTS `lzanotification__id`;
DELIMITER $$
CREATE TRIGGER `lzanotification__id` BEFORE INSERT ON `lzanotification` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
