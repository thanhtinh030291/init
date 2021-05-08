
-- --------------------------------------------------------

--
-- Table structure for table `lzaemail`
--

DROP TABLE IF EXISTS `lzaemail`;
CREATE TABLE `lzaemail` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `from_name` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `to_name` varchar(255) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `try` tinyint(3) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzaemail`
--

TRUNCATE TABLE `lzaemail`;
--
-- Triggers `lzaemail`
--
DROP TRIGGER IF EXISTS `lzaemail__id`;
DELIMITER $$
CREATE TRIGGER `lzaemail__id` BEFORE INSERT ON `lzaemail` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
