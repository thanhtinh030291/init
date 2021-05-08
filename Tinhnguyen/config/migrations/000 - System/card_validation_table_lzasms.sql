
-- --------------------------------------------------------

--
-- Table structure for table `lzasms`
--

DROP TABLE IF EXISTS `lzasms`;
CREATE TABLE `lzasms` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `receiver` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `try` tinyint(3) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzasms`
--

TRUNCATE TABLE `lzasms`;
--
-- Triggers `lzasms`
--
DROP TRIGGER IF EXISTS `lzasms__id`;
DELIMITER $$
CREATE TRIGGER `lzasms__id` BEFORE INSERT ON `lzasms` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
