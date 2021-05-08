
-- --------------------------------------------------------

--
-- Table structure for table `lzaapi`
--

DROP TABLE IF EXISTS `lzaapi`;
CREATE TABLE `lzaapi` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `username` char(50) NOT NULL,
  `password` char(64) NOT NULL,
  `permissions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzaapi`
--

TRUNCATE TABLE `lzaapi`;
--
-- Dumping data for table `lzaapi`
--

INSERT INTO `lzaapi` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `username`, `password`, `permissions`) VALUES
('747758a2-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile', 'f83e5c44dd0e351ded812799811d252347e15169065b9e49b825d5300e4d7ec2', '{\"api\":{\"get\":true,\"post\":true,\"patch\":true,\"put\":true,\"delete\":true}}');

--
-- Triggers `lzaapi`
--
DROP TRIGGER IF EXISTS `lzaapi__id`;
DELIMITER $$
CREATE TRIGGER `lzaapi__id` BEFORE INSERT ON `lzaapi` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
