
-- --------------------------------------------------------

--
-- Table structure for table `fubon_history`
--

DROP TABLE IF EXISTS `fubon_history`;
CREATE TABLE `fubon_history` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mantis_id` int(10) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `time` datetime(6) NOT NULL,
  `pocy_no` varchar(50) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `provider_id` char(36) NOT NULL,
  `incur_date` date NOT NULL,
  `diagnosis` text NOT NULL,
  `note` text NOT NULL,
  `result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_history`
--

TRUNCATE TABLE `fubon_history`;
--
-- Triggers `fubon_history`
--
DROP TRIGGER IF EXISTS `fubon_history__ai`;
DELIMITER $$
CREATE TRIGGER `fubon_history__ai` AFTER INSERT ON `fubon_history` FOR EACH ROW INSERT INTO fubon_history_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_history AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_history__au`;
DELIMITER $$
CREATE TRIGGER `fubon_history__au` AFTER UPDATE ON `fubon_history` FOR EACH ROW BEGIN
	DECLARE new_id char(36);
	SET new_id = NEW.id;
	UPDATE fubon_history_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = new_id AND d.valid_to IS NULL;
	INSERT INTO fubon_history_history
	SELECT 'Updated', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_history AS d
	WHERE d.id = new_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_history__bd`;
DELIMITER $$
CREATE TRIGGER `fubon_history__bd` BEFORE DELETE ON `fubon_history` FOR EACH ROW BEGIN
	DECLARE old_id char(36);
	SET old_id = OLD.id;
	UPDATE fubon_history_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = old_id AND d.valid_to IS NULL;
	INSERT INTO fubon_history_history
	SELECT 'Deleted', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_history AS d
	WHERE d.id = old_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_history__id`;
DELIMITER $$
CREATE TRIGGER `fubon_history__id` BEFORE INSERT ON `fubon_history` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
