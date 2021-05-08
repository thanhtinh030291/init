
-- --------------------------------------------------------

--
-- Table structure for table `lzafilter`
--

DROP TABLE IF EXISTS `lzafilter`;
CREATE TABLE `lzafilter` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(200) NOT NULL,
  `user_id` char(36) NOT NULL,
  `lzamodule_id` char(50) NOT NULL,
  `lzafield_id` char(36) NOT NULL,
  `selections` varchar(500) NOT NULL,
  `conditions` varchar(500) NOT NULL,
  `order_by` int(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzafilter`
--

TRUNCATE TABLE `lzafilter`;
--
-- Triggers `lzafilter`
--
DROP TRIGGER IF EXISTS `lzafilter__id`;
DELIMITER $$
CREATE TRIGGER `lzafilter__id` BEFORE INSERT ON `lzafilter` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
