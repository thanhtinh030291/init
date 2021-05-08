
-- --------------------------------------------------------

--
-- Table structure for table `lzahttprequest`
--

DROP TABLE IF EXISTS `lzahttprequest`;
CREATE TABLE `lzahttprequest` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `base_url` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `method` enum('get','post','put','patch','delete','options') NOT NULL,
  `headers` varchar(5000) NOT NULL,
  `data` longtext NOT NULL,
  `callback` char(255) DEFAULT NULL,
  `extra` text DEFAULT NULL,
  `next_try` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzahttprequest`
--

TRUNCATE TABLE `lzahttprequest`;
--
-- Triggers `lzahttprequest`
--
DROP TRIGGER IF EXISTS `lzahttprequest__id`;
DELIMITER $$
CREATE TRIGGER `lzahttprequest__id` BEFORE INSERT ON `lzahttprequest` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
