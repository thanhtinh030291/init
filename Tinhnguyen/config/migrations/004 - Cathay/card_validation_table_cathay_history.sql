
-- --------------------------------------------------------

--
-- Table structure for table `cathay_history`
--

DROP TABLE IF EXISTS `cathay_history`;
CREATE TABLE `cathay_history` (
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
-- Truncate table before insert `cathay_history`
--

TRUNCATE TABLE `cathay_history`;
--
-- Triggers `cathay_history`
--
DROP TRIGGER IF EXISTS `cathay_history__ai`;
DELIMITER $$
CREATE TRIGGER `cathay_history__ai` AFTER INSERT ON `cathay_history` FOR EACH ROW INSERT INTO cathay_history_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM cathay_history AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `cathay_history__au`;
DELIMITER $$
CREATE TRIGGER `cathay_history__au` AFTER UPDATE ON `cathay_history` FOR EACH ROW BEGIN
	DECLARE new_id char(36);
	SET new_id = NEW.id;
	UPDATE cathay_history_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = new_id AND d.valid_to IS NULL;
	INSERT INTO cathay_history_history
	SELECT 'Updated', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM cathay_history AS d
	WHERE d.id = new_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `cathay_history__bd`;
DELIMITER $$
CREATE TRIGGER `cathay_history__bd` BEFORE DELETE ON `cathay_history` FOR EACH ROW BEGIN
	DECLARE old_id char(36);
	SET old_id = OLD.id;
	UPDATE cathay_history_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = old_id AND d.valid_to IS NULL;
	INSERT INTO cathay_history_history
	SELECT 'Deleted', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM cathay_history AS d
	WHERE d.id = old_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `cathay_history__id`;
DELIMITER $$
CREATE TRIGGER `cathay_history__id` BEFORE INSERT ON `cathay_history` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
