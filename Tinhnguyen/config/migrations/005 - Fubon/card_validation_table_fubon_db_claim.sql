
-- --------------------------------------------------------

--
-- Table structure for table `fubon_db_claim`
--

DROP TABLE IF EXISTS `fubon_db_claim`;
CREATE TABLE `fubon_db_claim` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `db_ref_no` varchar(50) DEFAULT NULL,
  `fubon_history_id` char(36) NOT NULL,
  `fubon_head_id` char(36) NOT NULL,
  `pres_amt` int(10) UNSIGNED NOT NULL,
  `app_amt` int(10) UNSIGNED NOT NULL,
  `status` enum('Pending','Confirmed','Canceled','Deleted','Accepted','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_db_claim`
--

TRUNCATE TABLE `fubon_db_claim`;
--
-- Triggers `fubon_db_claim`
--
DROP TRIGGER IF EXISTS `fubon_db_claim__ai`;
DELIMITER $$
CREATE TRIGGER `fubon_db_claim__ai` AFTER INSERT ON `fubon_db_claim` FOR EACH ROW INSERT INTO fubon_db_claim_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_db_claim AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_db_claim__au`;
DELIMITER $$
CREATE TRIGGER `fubon_db_claim__au` AFTER UPDATE ON `fubon_db_claim` FOR EACH ROW BEGIN
	DECLARE new_id char(36);
	SET new_id = NEW.id;
	UPDATE fubon_db_claim_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = new_id AND d.valid_to IS NULL;
	INSERT INTO fubon_db_claim_history
	SELECT 'Updated', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_db_claim AS d
	WHERE d.id = new_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_db_claim__bd`;
DELIMITER $$
CREATE TRIGGER `fubon_db_claim__bd` BEFORE DELETE ON `fubon_db_claim` FOR EACH ROW BEGIN
	DECLARE old_id char(36);
	SET old_id = OLD.id;
	UPDATE fubon_db_claim_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = old_id AND d.valid_to IS NULL;
	INSERT INTO fubon_db_claim_history
	SELECT 'Deleted', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_db_claim AS d
	WHERE d.id = old_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_db_claim__id`;
DELIMITER $$
CREATE TRIGGER `fubon_db_claim__id` BEFORE INSERT ON `fubon_db_claim` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
