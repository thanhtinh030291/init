
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `card_validation`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `add_column_if_not_exists`$$
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `add_column_if_not_exists` (IN `in_table` VARCHAR(128), IN `in_column` VARCHAR(4096))  DETERMINISTIC BEGIN
    IF(
        0 = (
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = `in_table`
              AND column_name = `in_column`
        )
    ) THEN
        SET @s = CONCAT('ALTER TABLE `' ,`in_table` ,'` ADD ' ,`in_column`);
        PREPARE stmt FROM @s;
        EXECUTE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `drop_column_if_exists`$$
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `drop_column_if_exists` (IN `in_table` VARCHAR(128), IN `in_column` VARCHAR(128))  DETERMINISTIC BEGIN
    IF(
        0 < (
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = `in_table`
              AND column_name = `in_column`
        )
    ) THEN
        SET @s = CONCAT('ALTER TABLE `' ,`in_table` ,'` DROP COLUMN `' ,`in_column`,'`');
        PREPARE stmt FROM @s;
        EXECUTE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `drop_index_if_exists`$$
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `drop_index_if_exists` (IN `in_table` VARCHAR(128), IN `in_index` VARCHAR(128))  DETERMINISTIC BEGIN
    IF(
        0 < (
            SELECT COUNT(*)
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = `in_table`
              AND index_name = `in_index`
        )
    ) THEN
        SET @s = CONCAT('DROP INDEX `' ,`in_index` ,'` ON `' ,`in_table`,'`');
        PREPARE stmt FROM @s;
        EXECUTE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `modify_column_if_exists`$$
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `modify_column_if_exists` (IN `in_table` VARCHAR(128), IN `in_column` VARCHAR(4096))  DETERMINISTIC BEGIN
    IF(
        0 < (
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = `in_table`
              AND column_name = `in_column`
        )
    ) THEN
        SET @s = CONCAT('ALTER TABLE `' ,`in_table` ,'` MODIFY ' ,`in_column`);
        PREPARE stmt FROM @s;
        EXECUTE stmt;
    END IF;
END$$

--
-- Functions
--
DROP FUNCTION IF EXISTS `from_db_date`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `from_db_date` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS VARCHAR(20) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE RESULT VARCHAR(20);

    IF(`in_format` IS NULL) THEN
        SELECT DATE_FORMAT(`in_date`,`value`)
        FROM `lzasetting`
        WHERE `key` = 'date_format'
        INTO RESULT;
    ELSE
        SELECT DATE_FORMAT(`in_date`,`in_format`)
        INTO RESULT;
    END IF;

    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `from_db_datetime`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `from_db_datetime` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS VARCHAR(20) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE RESULT VARCHAR(20);

    IF(`in_format` IS NULL) THEN
        SELECT DATE_FORMAT(`in_date`,`value`)
        FROM `lzasetting`
        WHERE `key` = 'datetime_format'
        INTO RESULT;
    ELSE
        SELECT DATE_FORMAT(`in_date`,`in_format`)
        INTO RESULT;
    END IF;

    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `get_datetime_format`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_datetime_format` () RETURNS VARCHAR(30) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE RESULT VARCHAR(30);

    SELECT `value`
    FROM `lzasetting`
    WHERE `key` = 'datetime_format'
    INTO RESULT;

    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `get_date_format`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_date_format` () RETURNS VARCHAR(30) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE RESULT VARCHAR(30);

    SELECT `value`
    FROM `lzasetting`
    WHERE `key` = 'date_format'
    INTO RESULT;

    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `get_fubon_amt_limit_per_dis_day`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_fubon_amt_limit_per_dis_day` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3)) RETURNS INT(10) UNSIGNED DETERMINISTIC BEGIN
    DECLARE `amt_limit` INT(10) UNSIGNED;

    SET `in_mbr_no` = TRIM(LEADING '0' FROM `in_mbr_no`);

    SELECT DISTINCT `amt_per_dis_day`
    FROM `fubon_plan_limit` L
        JOIN `fubon_plan` P
          ON P.`id` = L.`fubon_plan_id`
        JOIN `fubon_member` M
          ON `P`.`name` = M.`plan_desc`
    WHERE L.`fubon_benefit_id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM M.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN M.`memb_eff_date`
                              AND IFNULL(M.`term_date`,M.`memb_exp_date`)
    INTO `amt_limit`;

    RETURN `amt_limit`;
END$$

DROP FUNCTION IF EXISTS `get_fubon_amt_limit_per_year`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_fubon_amt_limit_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3)) RETURNS INT(10) UNSIGNED DETERMINISTIC BEGIN
    DECLARE `amt_limit` INT(10) UNSIGNED;

    SET `in_mbr_no` = TRIM(LEADING '0' FROM `in_mbr_no`);

    SELECT DISTINCT `amt_per_year`
    FROM `fubon_plan_limit` L
        JOIN `fubon_plan` P
          ON P.`id` = L.`fubon_plan_id`
        JOIN `fubon_member` M
          ON `P`.`name` = M.`plan_desc`
    WHERE L.`fubon_benefit_id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM M.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN M.`memb_eff_date`
                              AND IFNULL(M.`term_date`,M.`memb_exp_date`)
    INTO `amt_limit`;

    RETURN `amt_limit`;
END$$

DROP FUNCTION IF EXISTS `get_fubon_ben_head`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_fubon_ben_head` (`in_ben_head` VARCHAR(14)) RETURNS VARCHAR(14) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE `ben_head` VARCHAR(14);

    SELECT `code`
    FROM `fubon_head`
    WHERE `code` = `in_ben_head`
    INTO `ben_head`;

    RETURN IFNULL(`ben_head`,'OPALL');
END$$

DROP FUNCTION IF EXISTS `get_pcv_amt_limit_per_year`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_pcv_amt_limit_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3)) RETURNS INT(10) UNSIGNED DETERMINISTIC BEGIN
    DECLARE `amt_limit` INT(10) UNSIGNED;

    SET `in_mbr_no` = TRIM(LEADING '0' FROM `in_mbr_no`);

    SELECT
        CASE
            WHEN `in_ben_id` = 2
                THEN `op_limit_per_year`
            WHEN `in_ben_id` = 3
                THEN `am_limit_per_year`
        END
    FROM `pcv_member`
    WHERE TRIM(LEADING '0' FROM M.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN M.`memb_eff_date`
                              AND IFNULL(M.`term_date`,M.`memb_exp_date`)
    INTO `amt_limit`;

    RETURN `amt_limit`;
END$$

DROP FUNCTION IF EXISTS `get_pcv_ben_head`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_pcv_ben_head` (`in_ben_head` VARCHAR(14)) RETURNS VARCHAR(14) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE `ben_head` VARCHAR(14);

    SELECT `code`
    FROM `pcv_head`
    WHERE `code` = `in_ben_head`
    INTO `ben_head`;

    RETURN IFNULL(`ben_head`,'OPALL');
END$$

DROP FUNCTION IF EXISTS `get_pcv_plan_desc`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_pcv_plan_desc` (`in_plan_desc` VARCHAR(255)) RETURNS VARCHAR(1000) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE `done` INT(1) DEFAULT 0;

    DECLARE `in_haystack` VARCHAR(20);
    DECLARE `in_needle` VARCHAR(100);
    DECLARE RESULT VARCHAR(1000);

    DECLARE `plan_desc_map_cursor` CURSOR FOR
        SELECT `haystack`,`needle`
        FROM `pcv_plan_desc_map`
        ORDER BY `order_by`;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET `done` = 1;

    SET RESULT = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`in_plan_desc`,'(',' ('),')',') '),' ,',','),',',','),'  ',' '),'/ ','/');

    OPEN `plan_desc_map_cursor`;
    `read_loop`:LOOP
        FETCH `plan_desc_map_cursor` INTO `in_haystack`,`in_needle`;
        IF `done` = 1 THEN
            LEAVE `read_loop`;
        END IF;
        SET RESULT = REPLACE(RESULT,`in_haystack`,`in_needle`);
    END LOOP;

    CLOSE `plan_desc_map_cursor`;
    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `get_remain_fubon_amt_per_dis_day`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_remain_fubon_amt_per_dis_day` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED, `in_diag_id` TINYINT(3) UNSIGNED) RETURNS INT(10) DETERMINISTIC BEGIN
    DECLARE `amt_limit` INT(10) UNSIGNED;
    DECLARE `used_benefit` INT(10) UNSIGNED;

    SET `amt_limit` = `get_fubon_amt_limit_per_dis_day`(
        `in_mbr_no`,`in_incur_date`,`in_ben_id`
    );
    SET `used_benefit` = `get_used_fubon_amt_per_dis_day`(
        `in_mbr_no`,`in_incur_date`,
        `in_ben_id`,`in_diag_id`
    );

    RETURN `amt_limit` - IFNULL(`used_benefit`,0);
END$$

DROP FUNCTION IF EXISTS `get_remain_fubon_amt_per_year`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_remain_fubon_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) DETERMINISTIC BEGIN
    DECLARE `amt_limit` INT(10) UNSIGNED;
    DECLARE `used_benefit` INT(10) UNSIGNED;

    SET `amt_limit` = `get_fubon_amt_limit_per_year`(
        `in_mbr_no`,`in_incur_date`,`in_ben_id`
    );
    SET `used_benefit` = `get_used_fubon_amt_per_year`(
        `in_mbr_no`,`in_incur_date`,`in_ben_id`
    );

    RETURN `amt_limit` - IFNULL(`used_benefit`,0);
END$$

DROP FUNCTION IF EXISTS `get_remain_pcv_amt_per_year`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_remain_pcv_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) DETERMINISTIC BEGIN
    DECLARE `amt_limit` INT(10) UNSIGNED;
    DECLARE `used_benefit` INT(10) UNSIGNED;

    SET `amt_limit` = `get_pcv_amt_limit_per_year`(
        `in_mbr_no`,`in_incur_date`,`in_ben_id`
    );
    SET `used_benefit` = `get_used_pcv_amt_per_year`(
        `in_mbr_no`,`in_incur_date`,`in_ben_id`
    );

    RETURN `amt_limit` - IFNULL(`used_benefit`,0);
END$$

DROP FUNCTION IF EXISTS `get_used_fubon_amt_per_dis_day`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_used_fubon_amt_per_dis_day` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED, `in_diag_id` TINYINT(3) UNSIGNED) RETURNS INT(10) UNSIGNED DETERMINISTIC BEGIN
    DECLARE `gop_amt` INT(10) UNSIGNED;
    DECLARE `claim_amt` INT(10) UNSIGNED;

    SET `in_mbr_no` = TRIM(LEADING '0' FROM `in_mbr_no`);

    SELECT SUM(G.`amount`) `amt`
    FROM `fubon_benefit` B
        INNER JOIN `fubon_benefit_fubon_head` BH
                ON B.`id` = BH.`fubon_benefit_id`
        INNER JOIN `fubon_head` H
                ON H.`id` = BH.`fubon_head_id`
        INNER JOIN `fubon_card_validation_history` G
                ON G.`fubon_head_id` = H.`id`
        INNER JOIN `fubon_diagnosis` D
                ON D.`id` = G.`fubon_diagnosis_id`
         LEFT JOIN `fubon_claim_line` C
                ON G.`id` = C.`db_ref_no`
    WHERE C.`id` IS NULL
      AND D.`id` = `in_diag_id`
      AND B.`id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM G.`mbr_no`) = `in_mbr_no`
      AND G.`incur_date` = `in_incur_date`
      AND G.`status` NOT IN ('Canceled','Deleted','Rejected')
    INTO `gop_amt`;

    SELECT SUM(C.`app_amt`) `amt`
    FROM `fubon_benefit` B
        JOIN `fubon_benefit_fubon_head` BH
          ON B.`id` = BH.`fubon_benefit_id`
        JOIN `fubon_head` H
          ON H.`id` = BH.`fubon_head_id`
        JOIN `fubon_claim_line` C
          ON GET_FUBON_BEN_HEAD(C.`ben_head`) = H.`code`
        JOIN `fubon_diagnosis` D
          ON D.`code` = C.`diag_code`
    WHERE B.`id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM C.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN C.`memb_eff_date`
                              AND IFNULL(C.`term_date`,C.`memb_exp_date`)
      AND C.`status` = 'AC'
      AND D.`id` = `in_diag_id`
    INTO `claim_amt`;

    RETURN IFNULL(`claim_amt`,0) + IFNULL(`gop_amt`,0);
END$$

DROP FUNCTION IF EXISTS `get_used_fubon_amt_per_year`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_used_fubon_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) UNSIGNED DETERMINISTIC BEGIN
    DECLARE `gop_amt` INT(10) UNSIGNED;
    DECLARE `claim_amt` INT(10) UNSIGNED;

    SET `in_mbr_no` = TRIM(LEADING '0' FROM `in_mbr_no`);

    SELECT SUM(G.`amount`) `amt`
    FROM `fubon_benefit` B
        INNER JOIN `fubon_benefit_fubon_head` BH
                ON B.`id` = BH.`fubon_benefit_id`
        INNER JOIN `fubon_head` H
                ON H.`id` = BH.`fubon_head_id`
        INNER JOIN `fubon_card_validation_history` G
                ON G.`fubon_head_id` = H.`id`
        INNER JOIN `fubon_member` M
                ON TRIM(LEADING '0' FROM M.`mbr_no`) = TRIM(LEADING '0' FROM G.`mbr_no`)
               AND G.`incur_date` BETWEEN M.`memb_eff_date`
                                      AND IFNULL(M.`term_date`,M.`memb_exp_date`)
         LEFT JOIN `fubon_claim_line` C
                ON G.`id` = C.`db_ref_no`
    WHERE C.`id` IS NULL
      AND B.`id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM G.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN M.`memb_eff_date`
                              AND IFNULL(M.`term_date`,M.`memb_exp_date`)
      AND G.`status` NOT IN ('Canceled','Deleted','Rejected')
    INTO `gop_amt`;

    SELECT SUM(C.`app_amt`) `amt`
    FROM `fubon_benefit` B
        JOIN `fubon_benefit_fubon_head` BH
          ON B.`id` = BH.`fubon_benefit_id`
        JOIN `fubon_head` H
          ON H.`id` = BH.`fubon_head_id`
        JOIN `fubon_claim_line` C
          ON GET_FUBON_BEN_HEAD(C.`ben_head`) = H.`code`
    WHERE B.`id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM C.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN C.`memb_eff_date`
                              AND IFNULL(C.`term_date`,C.`memb_exp_date`)
      AND C.`status` = 'AC'
    INTO `claim_amt`;

    RETURN IFNULL(`claim_amt`,0) + IFNULL(`gop_amt`,0);
END$$

DROP FUNCTION IF EXISTS `get_used_pcv_amt_per_year`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_used_pcv_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) UNSIGNED DETERMINISTIC BEGIN
    DECLARE `gop_amt` INT(10) UNSIGNED;
    DECLARE `claim_amt` INT(10) UNSIGNED;

    SET `in_mbr_no` = TRIM(LEADING '0' FROM `in_mbr_no`);

    SELECT SUM(G.`amount`) `amt`
    FROM `pcv_benefit` B
        INNER JOIN `pcv_benefit_pcv_head` BH
                ON B.`id` = BH.`pcv_benefit_id`
        INNER JOIN `pcv_head` H
                ON H.`id` = BH.`pcv_head_id`
        INNER JOIN `pcv_card_validation_history` G
                ON G.`pcv_head_id` = H.`id`
        INNER JOIN `pcv_member` M
                ON TRIM(LEADING '0' FROM M.`mbr_no`) = TRIM(LEADING '0' FROM G.`mbr_no`)
               AND G.`incur_date` BETWEEN M.`memb_eff_date`
                                      AND IFNULL(M.`term_date`,M.`memb_exp_date`)
         LEFT JOIN `pcv_claim_line` C
                ON G.`id` = C.`db_ref_no`
    WHERE C.`id` IS NULL
      AND B.`id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM G.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN M.`memb_eff_date`
                              AND IFNULL(M.`term_date`,M.`memb_exp_date`)
      AND G.`status` NOT IN ('Canceled','Deleted','Rejected')
    INTO `gop_amt`;

    SELECT SUM(C.`app_amt`) `amt`
    FROM `pcv_benefit` B
        JOIN `pcv_benefit_pcv_head` BH
          ON B.`id` = BH.`pcv_benefit_id`
        JOIN `pcv_head` H
          ON H.`id` = BH.`pcv_head_id`
        JOIN `pcv_claim_line` C
          ON GET_PCV_BEN_HEAD(C.`ben_head`) = H.`code`
    WHERE B.`id` = `in_ben_id`
      AND TRIM(LEADING '0' FROM C.`mbr_no`) = `in_mbr_no`
      AND `in_incur_date` BETWEEN C.`memb_eff_date`
                              AND IFNULL(C.`term_date`,C.`memb_exp_date`)
      AND C.`status` = 'AC'
    INTO `claim_amt`;

    RETURN IFNULL(`claim_amt`,0) + IFNULL(`gop_amt`,0);
END$$

DROP FUNCTION IF EXISTS `initcap`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `initcap` (`x` CHAR(255)) RETURNS CHAR(255) CHARSET utf8 DETERMINISTIC BEGIN
    SET @str='';
    SET @l_str='';
    WHILE x REGEXP ' ' DO
        SELECT SUBSTRING_INDEX(x,' ',1) INTO @l_str;
        SELECT SUBSTRING(x,LOCATE(' ',x) + 1) INTO x;
        SELECT CONCAT(
            @str,' ',
            CONCAT(
                UPPER(SUBSTRING(@l_str,1,1)),
                LOWER(SUBSTRING(@l_str,2))
            )
        )
        INTO @str;
    END WHILE;
    RETURN LTRIM(
        CONCAT(
            @str,' ',
            CONCAT(
                UPPER(SUBSTRING(x,1,1)),
                LOWER(SUBSTRING(x,2))
            )
        )
    );
END$$

DROP FUNCTION IF EXISTS `to_db_date`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `to_db_date` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS DATE DETERMINISTIC BEGIN
    DECLARE RESULT DATE;

    IF(`in_format` IS NULL) THEN
        SELECT STR_TO_DATE(`in_date`,`value`) FROM `lzasetting`
        WHERE `key` = 'date_format'
        INTO RESULT;
    ELSE
        SELECT STR_TO_DATE(`in_date`,`in_format`)
        INTO RESULT;
    END IF;

    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `to_db_datetime`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `to_db_datetime` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS DATETIME DETERMINISTIC BEGIN
    DECLARE RESULT DATETIME;

    IF(`in_format` IS NULL) THEN
        SELECT STR_TO_DATE(`in_date`,`value`) FROM `lzasetting`
        WHERE `key` = 'datetime_format'
        INTO RESULT;
    ELSE
        SELECT STR_TO_DATE(`in_date`,`in_format`)
        INTO RESULT;
    END IF;

    RETURN RESULT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cathay_benefit`
--

DROP TABLE IF EXISTS `cathay_benefit`;
CREATE TABLE `cathay_benefit` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `parent` char(36) DEFAULT NULL,
  `cathay_head_id` char(36) NOT NULL,
  `ben_type` varchar(10) NOT NULL,
  `ben_desc` varchar(500) NOT NULL,
  `ben_desc_vi` varchar(500) NOT NULL,
  `ben_note` varchar(500) NOT NULL,
  `ben_note_vi` varchar(500) NOT NULL,
  `is_combined` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_benefit`
--

TRUNCATE TABLE `cathay_benefit`;
--
-- Dumping data for table `cathay_benefit`
--

INSERT INTO `cathay_benefit` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `parent`, `cathay_head_id`, `ben_type`, `ben_desc`, `ben_desc_vi`, `ben_note`, `ben_note_vi`, `is_combined`) VALUES
('f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, 'f85e1b2d-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', 'Overall Maximum Limit Per Policy Year', 'Giới hạn tối đa cho 1 năm', 1),
('f860c52f-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e7607-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Outpatient Treatment (non-surgery)', 'Điều trị ngoại trú (không phẫu thuật)', 'fees for doctor, required diagnostic laboratory tests, imaging, prescribed medicines, medical supplies, and other related charges.<br />Co-pay 20:80 (Company pays 80%)', 'Bao gồm chi phí Bác sĩ, xét nghiệm chẩn đoán, chẩn đoán hình ảnh theo chỉ định của Bác sĩ, Thuốc được kê đơn, Vật tư y tế,  và các chi phí có liên quan khác. Đồng thanh toán 20:80 (Người được bảo hiểm tự trả 20% Chi phí hợp lý theo thông lệ).', NULL),
('f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e790c-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Outpatient Surgery (not Endoscopic surgery) Fee', 'Chi phí phẫu thuật ngoại trú (không bằng phương pháp nội soi)', 'fees for surgeon, operating room, anaesthetist, lab tests,  imaging, medical supplies, surgical appliances and devices, prescribed medicines, and other related charges.', 'Bao gồm chi phí bác sĩ phẫu thuật, chi phí phòng phẫu thuật, chi phí gây mê/gây tê, chi phí xét nghiệm, chẩn đoán hình ảnh, chi phí vật tư y tế, dụng cụ và trang thiết bị phẫu thuật, thuốc được kê đơn, và các chi phí có liên quan khác.', 1),
('f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e87b3-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Fee for Physiotherapy, Chiropractic in Outpatient Treatment when referred by Doctor', 'Chi phí vật lý trị liệu, trị liệu thần kinh cột sống trong điều trị ngoại trú  theo chỉ định của bác sĩ', 'maximum 30 days/year', 'tối đa 30 ngày/năm', 1),
('f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'f85e6ca7-4a71-11eb-a7cf-98fa9b10d0b1', 'OP', 'Dental Benefit', 'Điều trị răng', 'Co-pay 20:80 (Company pays 80%)<br />Covers the costs of:<ul><li>Examination, X-rays</li><li>Treatment of gingivitis, pyorrhoea</li><li>Root tip resection, Removal of calculus under gum</li><li>Tooth filling</li><li>Root canal treatment</li><li>Extraction</li><li>Tooth cleaning (maximum 1 time/year)</ul>', 'Đồng thanh toán 20:80 (Người được bảo hiểm tự trả 20% Chi phí hợp lý theo thông lệ). Công ty sẽ chi trả 80% Chi phí hợp lý theo thông lệ cho các chi phí sau:<ul><li>Khám, chụp X quang răng bệnh lý</li><li>Điều trị viêm nướu, nha chu</li><li>Cắt chóp răng, lấy u vôi răng (lấy vôi răng sâu dưới nướu)</li><li>Trám răng bệnh lý</li><li>Điều trị tủy răng</li><li>Nhổ răng bệnh lý</li><li>Cạo vôi răng (tối đa 1 lần/năm)</li></ul>', 1);

--
-- Triggers `cathay_benefit`
--
DROP TRIGGER IF EXISTS `cathay_benefit__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_benefit__id` BEFORE INSERT ON `cathay_benefit` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cathay_claim_line`
--

DROP TABLE IF EXISTS `cathay_claim_line`;
CREATE TABLE `cathay_claim_line` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `cl_no` varchar(20) NOT NULL,
  `db_ref_no` varchar(30) DEFAULT NULL,
  `incur_date_from` date NOT NULL,
  `ben_head` varchar(20) NOT NULL,
  `ben_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `diag_code` varchar(20) DEFAULT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `prov_name` varchar(255) DEFAULT NULL,
  `pres_amt` double UNSIGNED DEFAULT NULL,
  `app_amt` double UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_claim_line`
--

TRUNCATE TABLE `cathay_claim_line`;
--
-- Triggers `cathay_claim_line`
--
DROP TRIGGER IF EXISTS `cathay_claim_line__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_claim_line__id` BEFORE INSERT ON `cathay_claim_line` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cathay_claim_line2`
--

DROP TABLE IF EXISTS `cathay_claim_line2`;
CREATE TABLE `cathay_claim_line2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `cl_no` varchar(20) NOT NULL,
  `db_ref_no` varchar(30) DEFAULT NULL,
  `incur_date_from` date NOT NULL,
  `ben_head` varchar(20) NOT NULL,
  `ben_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `diag_code` varchar(20) DEFAULT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `prov_name` varchar(255) DEFAULT NULL,
  `pres_amt` double UNSIGNED DEFAULT NULL,
  `app_amt` double UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_claim_line2`
--

TRUNCATE TABLE `cathay_claim_line2`;
--
-- Triggers `cathay_claim_line2`
--
DROP TRIGGER IF EXISTS `cathay_claim_line2__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_claim_line2__id` BEFORE INSERT ON `cathay_claim_line2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cathay_db_claim`
--

DROP TABLE IF EXISTS `cathay_db_claim`;
CREATE TABLE `cathay_db_claim` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `db_ref_no` varchar(50) DEFAULT NULL,
  `cathay_history_id` char(36) NOT NULL,
  `cathay_head_id` char(36) NOT NULL,
  `pres_amt` int(10) UNSIGNED NOT NULL,
  `app_amt` int(10) UNSIGNED NOT NULL,
  `status` enum('Pending','Confirmed','Canceled','Deleted','Accepted','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_db_claim`
--

TRUNCATE TABLE `cathay_db_claim`;
--
-- Triggers `cathay_db_claim`
--
DROP TRIGGER IF EXISTS `cathay_db_claim__ai`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_db_claim__ai` AFTER INSERT ON `cathay_db_claim` FOR EACH ROW INSERT INTO cathay_db_claim_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM cathay_db_claim AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `cathay_db_claim__au`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_db_claim__au` AFTER UPDATE ON `cathay_db_claim` FOR EACH ROW BEGIN
	DECLARE new_id char(36);
	SET new_id = NEW.id;
	UPDATE cathay_db_claim_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = new_id AND d.valid_to IS NULL;
	INSERT INTO cathay_db_claim_history
	SELECT 'Updated', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM cathay_db_claim AS d
	WHERE d.id = new_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `cathay_db_claim__bd`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_db_claim__bd` BEFORE DELETE ON `cathay_db_claim` FOR EACH ROW BEGIN
	DECLARE old_id char(36);
	SET old_id = OLD.id;
	UPDATE cathay_db_claim_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = old_id AND d.valid_to IS NULL;
	INSERT INTO cathay_db_claim_history
	SELECT 'Deleted', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM cathay_db_claim AS d
	WHERE d.id = old_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `cathay_db_claim__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_db_claim__id` BEFORE INSERT ON `cathay_db_claim` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cathay_db_claim_history`
--

DROP TABLE IF EXISTS `cathay_db_claim_history`;
CREATE TABLE `cathay_db_claim_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `db_ref_no` varchar(50) DEFAULT NULL,
  `cathay_history_id` char(36) DEFAULT NULL,
  `cathay_head_id` char(36) DEFAULT NULL,
  `pres_amt` int(10) UNSIGNED DEFAULT NULL,
  `app_amt` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Confirmed','Canceled','Deleted','Accepted','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_db_claim_history`
--

TRUNCATE TABLE `cathay_db_claim_history`;
-- --------------------------------------------------------

--
-- Table structure for table `cathay_head`
--

DROP TABLE IF EXISTS `cathay_head`;
CREATE TABLE `cathay_head` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `cathay_benefit_id` char(36) NOT NULL,
  `code` varchar(10) NOT NULL,
  `ben_heads` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_vi` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_head`
--

TRUNCATE TABLE `cathay_head`;
--
-- Dumping data for table `cathay_head`
--

INSERT INTO `cathay_head` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `cathay_benefit_id`, `code`, `ben_heads`, `name`, `name_vi`) VALUES
('f85e1b2d-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860aa6e-4a71-11eb-a7cf-98fa9b10d0b1', 'OPALL', 'OP', 'OP Combined', 'Ngoại Trú Kết Hợp'),
('f85e68bc-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'CHIR', 'CHIR', 'Chiropractic', 'Phí Trị Liệu Thần Kinh Cột Sống'),
('f85e6ca7-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'DTALL', 'DENT,TCL', 'Dental Combined', 'Điều trị Nha Khoa Kết Hợp'),
('f85e6fe6-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'DENT', 'DENT', 'General Outpatient Dental Benefits', 'Phí Điều Trị Răng Tổng Quát'),
('f85e72f8-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'TCL', 'TCL', 'Toot Cleaning', 'Cạo Vôi Răng'),
('f85e7607-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c52f-4a71-11eb-a7cf-98fa9b10d0b1', 'OV', 'OV', 'Office Visit', 'Phí Bác Sĩ'),
('f85e790c-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'SURALL', 'SUR, OPR, ANES, OMIS', 'Surgery Combined', 'Phí Phẫu Thuật Kết Hợp'),
('f85e7c05-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'SUR', 'SUR', 'Surgery', 'Phí Bác Sĩ Phẫu Thuật'),
('f85e7eed-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f8613261-4a71-11eb-a7cf-98fa9b10d0b1', 'OPR', 'OPR', 'Operating Room', 'Phí Phòng Phẫu Thuật'),
('f85e81f2-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'ANES', 'ANES', 'Anaesthetist', 'Phí Gây Mê/Tê'),
('f85e84c7-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860c959-4a71-11eb-a7cf-98fa9b10d0b1', 'OMIS', 'OMIS', 'Medical Supplies', 'Phí Vật Tư Y Tế'),
('f85e87b3-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'PHYSALL', 'PHYS,CHIR', 'Physiotherapy Combined', 'Phí Vật Lý Trị Liệu Kết Hợp'),
('f85e8a66-4a71-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'f860ccaf-4a71-11eb-a7cf-98fa9b10d0b1', 'PHYS', 'PHYS', 'Physiotherapy', 'Phí Vật Lý Trị Liệu');

--
-- Triggers `cathay_head`
--
DROP TRIGGER IF EXISTS `cathay_head__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_head__id` BEFORE INSERT ON `cathay_head` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_history__ai` AFTER INSERT ON `cathay_history` FOR EACH ROW INSERT INTO cathay_history_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM cathay_history AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `cathay_history__au`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_history__au` AFTER UPDATE ON `cathay_history` FOR EACH ROW BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_history__bd` BEFORE DELETE ON `cathay_history` FOR EACH ROW BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_history__id` BEFORE INSERT ON `cathay_history` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cathay_history_history`
--

DROP TABLE IF EXISTS `cathay_history_history`;
CREATE TABLE `cathay_history_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `mantis_id` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(30) DEFAULT NULL,
  `time` datetime(6) NOT NULL,
  `pocy_no` varchar(50) DEFAULT NULL,
  `mbr_no` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `provider_id` char(36) DEFAULT NULL,
  `incur_date` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_history_history`
--

TRUNCATE TABLE `cathay_history_history`;
-- --------------------------------------------------------

--
-- Table structure for table `cathay_member`
--

DROP TABLE IF EXISTS `cathay_member`;
CREATE TABLE `cathay_member` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `memb_ref_no` varchar(20) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `min_memb_eff_date` date NOT NULL,
  `min_pocy_eff_date` date NOT NULL,
  `insured_periods` varchar(500) NOT NULL,
  `wait_period` enum('Yes','No') NOT NULL,
  `spec_dis_period` enum('Yes','No') NOT NULL,
  `product` varchar(10) NOT NULL,
  `plan_desc` varchar(255) NOT NULL,
  `memb_rstr` text DEFAULT NULL,
  `memb_rstr_vi` text DEFAULT NULL,
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_member`
--

TRUNCATE TABLE `cathay_member`;
--
-- Triggers `cathay_member`
--
DROP TRIGGER IF EXISTS `cathay_member__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_member__id` BEFORE INSERT ON `cathay_member` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cathay_member2`
--

DROP TABLE IF EXISTS `cathay_member2`;
CREATE TABLE `cathay_member2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `memb_ref_no` varchar(20) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `min_memb_eff_date` date NOT NULL,
  `min_pocy_eff_date` date NOT NULL,
  `insured_periods` varchar(500) NOT NULL,
  `wait_period` enum('Yes','No') NOT NULL,
  `spec_dis_period` enum('Yes','No') NOT NULL,
  `product` varchar(10) NOT NULL,
  `plan_desc` varchar(255) NOT NULL,
  `memb_rstr` text DEFAULT NULL,
  `memb_rstr_vi` text DEFAULT NULL,
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `cathay_member2`
--

TRUNCATE TABLE `cathay_member2`;
--
-- Triggers `cathay_member2`
--
DROP TRIGGER IF EXISTS `cathay_member2__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `cathay_member2__id` BEFORE INSERT ON `cathay_member2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `form`
--

DROP TABLE IF EXISTS `form`;
CREATE TABLE `form` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL,
  `name_vi` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `path_vi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `form`
--

TRUNCATE TABLE `form`;
--
-- Dumping data for table `form`
--

INSERT INTO `form` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `name_vi`, `path`, `path_vi`) VALUES
('9a4c852c-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Claim Form', 'Đơn Yêu Cầu Bồi Thường', 'resources/files/claim-form-en.pdf', 'resources/files/claim-form-vi.pdf'),
('9a4cc943-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'GOP Form', 'Đơn Yêu Cầu Bảo Lãnh Viện Phí', 'resources/files/gop-form.pdf', 'resources/files/gop-form.pdf'),
('9a4cd0d4-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Dental Examine Report', 'Báo Cáo Tổn Thương Nha Khoa', 'resources/files/dental-exam-report-en.pdf', 'resources/files/dental-exam-report-vi.pdf'),
('9a4d49fa-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Oral Examine Report', 'Báo Cáo Kiểm Tra Răng Miệng', 'resources/files/oral-exam-report-en.pdf', 'resources/files/oral-exam-report-vi.pdf'),
('9a4d4dea-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Incident Report', 'Báo Cáo Tai Nạn', 'resources/files/incident-report-en.pdf', 'resources/files/incident-report-vi.pdf'),
('9a4db724-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Progress Note', 'Phiếu  Theo Dõi Diễn Tiến Trị Liệu', 'resources/files/progress-note-en.pdf', 'resources/files/progress-note-vi.pdf'),
('9a4dba9d-4a68-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Treatment Plan', 'Kế Hoạch Điều Trị', 'resources/files/treatment-plan-en.pdf', 'resources/files/treatment-plan-vi.pdf');

--
-- Triggers `form`
--
DROP TRIGGER IF EXISTS `form__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `form__id` BEFORE INSERT ON `form` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_benefit`
--

DROP TABLE IF EXISTS `fubon_benefit`;
CREATE TABLE `fubon_benefit` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `parent` char(36) DEFAULT NULL,
  `ben_type` varchar(10) NOT NULL,
  `ben_desc` varchar(500) NOT NULL,
  `ben_desc_vi` varchar(500) NOT NULL,
  `ben_note` varchar(500) NOT NULL,
  `ben_note_vi` varchar(500) NOT NULL,
  `fubon_head_id` char(36) NOT NULL,
  `is_combined` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_benefit`
--

TRUNCATE TABLE `fubon_benefit`;
--
-- Dumping data for table `fubon_benefit`
--

INSERT INTO `fubon_benefit` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `parent`, `ben_type`, `ben_desc`, `ben_desc_vi`, `ben_note`, `ben_note_vi`, `fubon_head_id`, `is_combined`) VALUES
('df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', 'Overall Maximum Limit Per Policy Year', 'Giới hạn tối đa cho 1 năm', 'df20f02a-4a72-11eb-a7cf-98fa9b10d0b1', 1),
('df2533d0-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Consultation fees', 'Chi phí khám', '(Primary and Specialist Care)', '(Khám Tổng quát và Chuyên sâu)', 'df227086-4a72-11eb-a7cf-98fa9b10d0b1', NULL),
('df25383a-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Miscellaneous charges', 'Chi phí y tế khác', '(Relating to Primary and Specialist Care)<br /> * Diagnostic procedures (blood test,x-ray,…)<br /> * Drugs and dressings', '(Điều trị Tổng quát và Chuyên sâu)<br /> * Chi phí chẩn đoán (xét nghiệm máu,chụp x-ray,…)<br /> * Thuốc và vật dụng băng bó', 'df22859c-4a72-11eb-a7cf-98fa9b10d0b1', 1),
('df253cd6-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Alternative Treatment', 'Điều trị hỗ trợ', '(Consultation fees and treatment provided and prescribed by a qualified and registered chiropractor,podiatrist,dietitian,naturopath,acupuncturist,homeopath,osteopath,physiotherapist and traditional Chinese medicine practitioner)', '(Khám và điều trị được bác sĩ chỉ định  và được thực hiện bởi chuyên viên châm cứu,trị liệu cột sống,chuyên viên dinh dưỡng,trị liệu bằng phương pháp vi lượng đồng căn, trị liệu thiên nhiên,nắn xương khớp,vật lí trị liệu và Y học Trung Hoa có giấy phép hành nghề hợp pháp)', 'df228b95-4a72-11eb-a7cf-98fa9b10d0b1', NULL),
('df258731-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Accidental Damage to Natural Teeth', 'Điều trị răng bị tổn thương do tai nạn', '', '', 'df228e30-4a72-11eb-a7cf-98fa9b10d0b1', NULL),
('df258a73-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1', 'OP', 'Routine and Preventive Dental Care', 'Chăm sóc và điều trị răng', '', '', 'df22909a-4a72-11eb-a7cf-98fa9b10d0b1', NULL);

--
-- Triggers `fubon_benefit`
--
DROP TRIGGER IF EXISTS `fubon_benefit__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_benefit__id` BEFORE INSERT ON `fubon_benefit` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_claim_line`
--

DROP TABLE IF EXISTS `fubon_claim_line`;
CREATE TABLE `fubon_claim_line` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `cl_no` varchar(20) NOT NULL,
  `db_ref_no` varchar(30) DEFAULT NULL,
  `incur_date_from` date NOT NULL,
  `ben_head` varchar(20) NOT NULL,
  `ben_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `diag_code` varchar(20) DEFAULT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `prov_name` varchar(255) DEFAULT NULL,
  `pres_amt` double UNSIGNED DEFAULT NULL,
  `app_amt` double UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_claim_line`
--

TRUNCATE TABLE `fubon_claim_line`;
--
-- Triggers `fubon_claim_line`
--
DROP TRIGGER IF EXISTS `fubon_claim_line__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_claim_line__id` BEFORE INSERT ON `fubon_claim_line` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_claim_line2`
--

DROP TABLE IF EXISTS `fubon_claim_line2`;
CREATE TABLE `fubon_claim_line2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `cl_no` varchar(20) NOT NULL,
  `db_ref_no` varchar(30) DEFAULT NULL,
  `incur_date_from` date NOT NULL,
  `ben_head` varchar(20) NOT NULL,
  `ben_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `diag_code` varchar(20) DEFAULT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `prov_name` varchar(255) DEFAULT NULL,
  `pres_amt` double UNSIGNED DEFAULT NULL,
  `app_amt` double UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_claim_line2`
--

TRUNCATE TABLE `fubon_claim_line2`;
--
-- Triggers `fubon_claim_line2`
--
DROP TRIGGER IF EXISTS `fubon_claim_line2__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_claim_line2__id` BEFORE INSERT ON `fubon_claim_line2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_client`
--

DROP TABLE IF EXISTS `fubon_client`;
CREATE TABLE `fubon_client` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `poho_no` varchar(20) DEFAULT NULL,
  `poho_name` varchar(255) DEFAULT NULL,
  `tax_code` varchar(255) DEFAULT NULL,
  `pocy_type` varchar(255) DEFAULT NULL,
  `pocy_no` varchar(20) DEFAULT NULL,
  `pre_pocy_no` varchar(20) DEFAULT NULL,
  `min_pocy_no` varchar(20) DEFAULT NULL,
  `min_pocy_eff_date` datetime DEFAULT NULL,
  `pocy_status` varchar(255) DEFAULT NULL,
  `pocy_eff_date` datetime DEFAULT NULL,
  `pocy_exp_date` datetime DEFAULT NULL,
  `payment_mode` varchar(20) DEFAULT NULL,
  `prem_amt` varchar(20) DEFAULT NULL,
  `mbr_name` varchar(255) DEFAULT NULL,
  `memb_status` varchar(255) DEFAULT NULL,
  `memb_eff_date` datetime DEFAULT NULL,
  `memb_exp_date` datetime DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `id_card_no` varchar(20) DEFAULT NULL,
  `citizen` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `plan` varchar(255) DEFAULT NULL,
  `sum_insured` double DEFAULT NULL,
  `ben_1` double DEFAULT NULL,
  `ben_2` double DEFAULT NULL,
  `ben_3` double DEFAULT NULL,
  `ben_4` double DEFAULT NULL,
  `ben_5` double DEFAULT NULL,
  `ben_6` double DEFAULT NULL,
  `exclusion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_client`
--

TRUNCATE TABLE `fubon_client`;
--
-- Triggers `fubon_client`
--
DROP TRIGGER IF EXISTS `fubon_client__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_client__id` BEFORE INSERT ON `fubon_client` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_client2`
--

DROP TABLE IF EXISTS `fubon_client2`;
CREATE TABLE `fubon_client2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `poho_no` varchar(20) NOT NULL,
  `poho_name` varchar(255) NOT NULL,
  `tax_code` varchar(255) DEFAULT NULL,
  `pocy_type` varchar(255) NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pre_pocy_no` varchar(20) NOT NULL,
  `min_pocy_no` varchar(20) NOT NULL,
  `min_pocy_eff_date` datetime NOT NULL,
  `pocy_status` varchar(255) NOT NULL,
  `pocy_eff_date` datetime NOT NULL,
  `pocy_exp_date` datetime NOT NULL,
  `payment_mode` varchar(20) DEFAULT NULL,
  `prem_amt` varchar(20) NOT NULL,
  `mbr_name` varchar(255) NOT NULL,
  `memb_status` varchar(255) NOT NULL,
  `memb_eff_date` datetime NOT NULL,
  `memb_exp_date` datetime NOT NULL,
  `gender` varchar(10) NOT NULL,
  `dob` date NOT NULL,
  `id_card_no` varchar(20) NOT NULL,
  `citizen` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `plan` varchar(255) NOT NULL,
  `sum_insured` double NOT NULL,
  `ben_1` double DEFAULT NULL,
  `ben_2` double DEFAULT NULL,
  `ben_3` double DEFAULT NULL,
  `ben_4` double DEFAULT NULL,
  `ben_5` double DEFAULT NULL,
  `ben_6` double DEFAULT NULL,
  `exclusion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_client2`
--

TRUNCATE TABLE `fubon_client2`;
--
-- Triggers `fubon_client2`
--
DROP TRIGGER IF EXISTS `fubon_client2__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_client2__id` BEFORE INSERT ON `fubon_client2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_db_claim__ai` AFTER INSERT ON `fubon_db_claim` FOR EACH ROW INSERT INTO fubon_db_claim_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_db_claim AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_db_claim__au`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_db_claim__au` AFTER UPDATE ON `fubon_db_claim` FOR EACH ROW BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_db_claim__bd` BEFORE DELETE ON `fubon_db_claim` FOR EACH ROW BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_db_claim__id` BEFORE INSERT ON `fubon_db_claim` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_db_claim_history`
--

DROP TABLE IF EXISTS `fubon_db_claim_history`;
CREATE TABLE `fubon_db_claim_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `db_ref_no` varchar(50) DEFAULT NULL,
  `fubon_history_id` char(36) DEFAULT NULL,
  `fubon_head_id` char(36) DEFAULT NULL,
  `pres_amt` int(10) UNSIGNED DEFAULT NULL,
  `app_amt` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Confirmed','Canceled','Deleted','Accepted','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_db_claim_history`
--

TRUNCATE TABLE `fubon_db_claim_history`;
-- --------------------------------------------------------

--
-- Table structure for table `fubon_head`
--

DROP TABLE IF EXISTS `fubon_head`;
CREATE TABLE `fubon_head` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `code` varchar(10) NOT NULL,
  `ben_heads` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_vi` varchar(500) NOT NULL,
  `fubon_benefit_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_head`
--

TRUNCATE TABLE `fubon_head`;
--
-- Dumping data for table `fubon_head`
--

INSERT INTO `fubon_head` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `code`, `ben_heads`, `name`, `name_vi`, `fubon_benefit_id`) VALUES
('df20f02a-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'OPALL', 'OP', 'OP Combined', 'Ngoại Trú Kết Hợp', 'df252b84-4a72-11eb-a7cf-98fa9b10d0b1'),
('df227086-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'OV', 'OV', 'Office Visit', 'Phí Bác Sĩ', 'df2533d0-4a72-11eb-a7cf-98fa9b10d0b1'),
('df22859c-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'OVRX', 'OVRX', 'Diagnosis', 'Chi phí Chẩn đoán', 'df25383a-4a72-11eb-a7cf-98fa9b10d0b1'),
('df228b95-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'PHYS', 'PHYS', 'Physiotherapist', 'Chi phí vật lý trị liệu', 'df253cd6-4a72-11eb-a7cf-98fa9b10d0b1'),
('df228e30-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'TDAM', 'TDAM', 'Accidental Teeth Damage', 'Điều trị cấp cứu tổn thương răng do Tai nạn', 'df258731-4a72-11eb-a7cf-98fa9b10d0b1'),
('df22909a-4a72-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'DENT', 'DENT', 'General outpatient dental benefits', 'Quyền lợi cho răng ngoại trú tổng quát', 'df258a73-4a72-11eb-a7cf-98fa9b10d0b1');

--
-- Triggers `fubon_head`
--
DROP TRIGGER IF EXISTS `fubon_head__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_head__id` BEFORE INSERT ON `fubon_head` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_history__ai` AFTER INSERT ON `fubon_history` FOR EACH ROW INSERT INTO fubon_history_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM fubon_history AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `fubon_history__au`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_history__au` AFTER UPDATE ON `fubon_history` FOR EACH ROW BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_history__bd` BEFORE DELETE ON `fubon_history` FOR EACH ROW BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_history__id` BEFORE INSERT ON `fubon_history` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_history_history`
--

DROP TABLE IF EXISTS `fubon_history_history`;
CREATE TABLE `fubon_history_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `mantis_id` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(30) DEFAULT NULL,
  `time` datetime(6) NOT NULL,
  `pocy_no` varchar(50) DEFAULT NULL,
  `mbr_no` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `provider_id` char(36) DEFAULT NULL,
  `incur_date` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_history_history`
--

TRUNCATE TABLE `fubon_history_history`;
-- --------------------------------------------------------

--
-- Table structure for table `fubon_member`
--

DROP TABLE IF EXISTS `fubon_member`;
CREATE TABLE `fubon_member` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `mbr_name_2` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `min_memb_eff_date` date NOT NULL,
  `min_pocy_eff_date` date NOT NULL,
  `insured_periods` varchar(500) NOT NULL,
  `wait_period` enum('Yes','No') NOT NULL,
  `spec_dis_period` enum('Yes','No') NOT NULL,
  `product` varchar(10) NOT NULL,
  `plan_desc` varchar(255) NOT NULL,
  `memb_rstr` text DEFAULT NULL,
  `memb_rstr_vi` text DEFAULT NULL,
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_member`
--

TRUNCATE TABLE `fubon_member`;
--
-- Triggers `fubon_member`
--
DROP TRIGGER IF EXISTS `fubon_member__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_member__id` BEFORE INSERT ON `fubon_member` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fubon_member2`
--

DROP TABLE IF EXISTS `fubon_member2`;
CREATE TABLE `fubon_member2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `mbr_name_2` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `min_memb_eff_date` date NOT NULL,
  `min_pocy_eff_date` date NOT NULL,
  `insured_periods` varchar(500) NOT NULL,
  `wait_period` enum('Yes','No') NOT NULL,
  `spec_dis_period` enum('Yes','No') NOT NULL,
  `product` varchar(10) NOT NULL,
  `plan_desc` varchar(255) NOT NULL,
  `memb_rstr` text DEFAULT NULL,
  `memb_rstr_vi` text DEFAULT NULL,
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `fubon_member2`
--

TRUNCATE TABLE `fubon_member2`;
--
-- Triggers `fubon_member2`
--
DROP TRIGGER IF EXISTS `fubon_member2__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `fubon_member2__id` BEFORE INSERT ON `fubon_member2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzaapi__id` BEFORE INSERT ON `lzaapi` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzaemail__id` BEFORE INSERT ON `lzaemail` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzafield`
--

DROP TABLE IF EXISTS `lzafield`;
CREATE TABLE `lzafield` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzamodule_id` char(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `single` varchar(50) NOT NULL,
  `plural` varchar(50) NOT NULL,
  `single_vi` varchar(50) NOT NULL,
  `plural_vi` varchar(50) NOT NULL,
  `type` enum('integer','float','double','text','textarea','html','password','email','phone','link','enum','checkbox','file','date','datetime','eventstart','eventend','self','belong','weakbelong','has','have','sequence','json','object') NOT NULL DEFAULT 'text',
  `mandatory` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_unique` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `minlength` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `maxlength` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `regex` varchar(200) NOT NULL,
  `error` varchar(200) NOT NULL,
  `order_by` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `level` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `statistic` varchar(100) NOT NULL DEFAULT '',
  `display` varchar(500) NOT NULL DEFAULT '',
  `note` varchar(500) NOT NULL,
  `note_vi` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzafield`
--

TRUNCATE TABLE `lzafield`;
--
-- Dumping data for table `lzafield`
--

INSERT INTO `lzafield` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzamodule_id`, `field`, `single`, `plural`, `single_vi`, `plural_vi`, `type`, `mandatory`, `is_unique`, `minlength`, `maxlength`, `regex`, `error`, `order_by`, `level`, `statistic`, `display`, `note`, `note_vi`) VALUES
('8f39b8aa-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f39cc7e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'expiry', 'Expiry Date', 'Expiry Dates', 'Ngày Hết hạn', 'Ngày Hết hạn', 'datetime', 0, 0, 0, 0, '', '', 10, 3, '', 'fullname', 'Specify when this user''s password expired', 'Xác định lúc nào mật khẩu của người dùng này hết hạn'),
('8f39cdb7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'last_reset_by', 'Last Reset By', 'Last Reset By', 'Đặt lại Mật khẩu lần cuối bởi', 'Đặt lại Mật khẩu lần cuối bởi', 'text', 0, 0, 0, 200, '', '', 11, 2, '', '', 'Specify who reset this user''s password', 'Xác định ai đặt lại mật khẩu cho người dùng này'),
('8f39ce65-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của Bồi Thường'),
('8f39cf18-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'mbr_no', 'Member No', 'Member Nos', 'Số Thành viên', 'Số Thành viên', 'text', 1, 1, 0, 20, '', '', 2, 3, '', '', 'Number of the Member', 'Số của Thành viên'),
('8f39cfb4-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'dob', 'Birth Date', 'Birth Dates', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 1, 0, 0, '', '', 3, 2, '', '', 'Birth Date of the Member', 'Ngày Sinh của Thành viên'),
('8f39d047-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'memb_eff_date', 'Effective Date', 'Effective Dates', 'Ngày Hiệu lực', 'Ngày Hiệu lực', 'date', 1, 1, 0, 0, '', '', 4, 2, '', '', 'Effective Date of the Member', 'Ngày Hiệu lực của Thành viên'),
('8f39d0d9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'memb_exp_date', 'Expiry Date', 'Expiry Dates', 'Ngày Kết thúc', 'Ngày Kết thúc', 'date', 1, 1, 0, 0, '', '', 5, 2, '', '', 'Expiry Date of the Member', 'Ngày Kết thúc của Thành viên'),
('8f39d16c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'last_reset_at', 'Last Reset At', 'Last Reset At', 'Đặt lại Mật khẩu lần cuối lúc', 'Đặt lại Mật khẩu lần cuối lúc', 'datetime', 0, 0, 0, 1, '', '', 12, 2, '', 'fullname', 'Specify when this user''s password reseted', 'Xác định khi nào người dùng này được đặt lại mật khẩu'),
('8f39d1fd-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'term_date', 'Termination Date', 'Termination Dates', 'Ngày Chấm dứt', 'Ngày Chấm dứt', 'date', 1, 1, 0, 0, '', '', 6, 2, '', '', 'Termination Date of the Member', 'Ngày Chấm dứt của Thành viên'),
('8f39d289-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'cl_no', 'Claim No', 'Claim Nos', 'Số Bồi Thường', 'Số Bồi Thường', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'Claim No of the claim', 'Số Bồi Thường của Bồi Thường'),
('8f39d31d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'db_ref_no', 'DB Ref No', 'DB Ref Nos', 'Số Tham Chiếu Bồi Thường', 'Số Tham Chiếu Bồi Thường', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'DB Ref No of the claim', 'Số Tham Chiếu Bồi Thường của Bồi Thường'),
('8f39d3af-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'incur_date_from', 'Incur Date From', 'Incur Date Froms', 'Ngày Nhập viện', 'Ngày Nhập viện', 'date', 1, 1, 0, 0, '', '', 8, 3, '', '', 'Incur Date From of the claim', 'Ngày Nhập viện của Bồi Thường'),
('8f39d43c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'ben_head', 'Benefit Head', 'Benefit Heads', 'Mã Quyền Lợi', 'Mã Quyền Lợi', 'text', 1, 1, 0, 10, '', '', 9, 2, '', '', 'Benefit Head of the claim', 'Mã Quyền Lợi của Bồi Thường'),
('8f39d4cc-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'status', 'Status', 'Statuses', 'Trạng Thái', 'Trạng Thái', 'text', 1, 1, 0, 10, '', '', 10, 2, '', '', 'Status of the claim', 'Trạng Thái của Bồi Thường'),
('8f39d550-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'prov_code', 'Provider Code', 'Provider Codes', 'Mã Nhà Cung Cấp', 'Mã Nhà Cung Cấp', 'text', 0, 1, 0, 255, '', '', 11, 2, '', '', 'Provider Code of the claim', 'Mã Nhà Cung Cấp của Bồi Thường'),
('8f39d5d0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'prov_name', 'Provider Name', 'Provider Names', 'Tên Nhà Cung Cấp', 'Tên Nhà Cung Cấp', 'text', 0, 1, 0, 255, '', '', 12, 2, '', '', 'Provider Name of the claim', 'Tên Nhà Cung Cấp của Bồi Thường'),
('8f39d651-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'pres_amt', 'Presented Amount', 'Presented Amount', 'Số tiền Yêu Cầu', 'Số tiền Yêu Cầu', 'double', 1, 0, 0, 0, '', '', 13, 2, '', '', 'Presented Amount of the claim', 'Số tiền Yêu cầu của Bồi Thường'),
('8f39d6d5-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_claim_line', 'app_amt', 'Approved Amount', 'Approved Amount', 'Số tiền Chấp Nhận', 'Số tiền Chấp Nhận', 'double', 1, 0, 0, 0, '', '', 14, 2, '', '', 'Approved Amount of the claim', 'Số tiền Chấp Nhận của Bồi Thường'),
('8f39d754-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'post', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f39d7df-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_plan_desc_map', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f39d875-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_plan_desc_map', 'haystack', 'Haystack', 'Haystacks', 'Chuỗi Cần Thay', 'Chuỗi Cần Thay', 'text', 1, 1, 0, 0, '', '', 2, 15, '', '', 'Haystack', 'Chuỗi Cần Thay'),
('8f39d901-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_plan_desc_map', 'needle', 'Needle', 'Needles', 'Chuỗi Để Thay', 'Trạng thái', 'text', 1, 0, 0, 200, '', '', 3, 15, '', '', 'Needle', 'Chuỗi Để Thay'),
('8f39d98b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_plan_desc_map', 'order_by', 'Order', 'Orders', 'Thứ Tự', 'Thứ Tự', 'sequence', 1, 0, 0, 0, '', '', 4, 15, '', '', 'Order ò the Item', 'Thứ tự của mục'),
('8f39da16-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_head', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f39daed-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_head', 'code', 'Code', 'Codes', 'Mã', 'Mã', 'text', 1, 1, 0, 0, '', '', 2, 15, '', '', 'Code', 'Mã'),
('8f39db91-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_head', 'ben_heads', 'Ben Heads', 'Ben Heads', 'Quyền Lợi', 'Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Ben Heads', 'Quyền Lợi'),
('8f39dc1c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_head', 'name', 'Name', 'Names', 'Tên', 'Tên', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Name', 'Tên'),
('8f39dca8-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_head', 'name_vi', 'Name (VN)', 'Names (VN)', 'Tên (VN)', 'Tên (VN)', 'text', 1, 1, 0, 0, '', '', 5, 15, '', '', 'Name (VN)', 'Tên (VN)'),
('8f39dd33-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_head', 'fubon_benefit', 'Primary Benefit', 'Primary Benefits', 'Quyền Lợi Chính', 'Quyền Lợi Chính', 'belong', 1, 0, 0, 0, '', '', 6, 15, 'ben_desc', 'ben_desc', 'Primary Benefit', 'Quyền Lợi Chính'),
('8f39ddb7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'post', 'slug', 'Slug', 'Slugs', 'Mã trang', 'Mã trang', 'text', 1, 1, 0, 200, '', '', 2, 15, '', '', 'The end part of the page''s URL,after domain name', 'Phần cuối URL của trang,sau tên miền'),
('8f39de42-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 3, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f39ded9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'parent', 'Parent', 'Parents', 'Mục Cha', 'Mục Cha', 'self', 0, 0, 0, 0, '', '', 2, 15, '', 'ben_desc', 'Parent', 'Mục Cha'),
('8f39df60-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'ben_type', 'Benefit Type', 'Benefit Types', 'Loại Quyền Lợi', 'Loại Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Benefit Type', 'Loại Quyền Lợi'),
('8f39dfe7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'ben_desc', 'Benefit Description', 'Benefit Descriptions', 'Mô Tả Quyền Lợi', 'Mô Tả Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Benefit Description', 'Mô Tả Quyền Lợi'),
('8f39e09a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'ben_desc_vi', 'Benefit Description (VN)', 'Benefit Descriptions (VN)', 'Mô Tả Quyền Lợi (VN)', 'Mô Tả Quyền Lợi (VN)', 'text', 1, 1, 0, 0, '', '', 5, 15, '', '', 'Benefit Description (VN)', 'Mô Tả Quyền Lợi (VN)'),
('8f39e12b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'ben_note', 'Benefit Note', 'Benefit Notes', 'Ghi Chú Quyền Lợi', 'Ghi Chú Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 6, 15, '', '', 'Benefit Note', 'Ghi Chú Quyền Lợi'),
('8f39e1ac-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'ben_note_vi', 'Benefit Note (VN)', 'Benefit Notes (VN)', 'Ghi Chú Quyền Lợi (VN)', 'Ghi Chú Quyền Lợi (VN)', 'text', 1, 1, 0, 0, '', '', 7, 15, '', '', 'Benefit Note (VN)', 'Ghi Chú Quyền Lợi (VN)'),
('8f3a3e94-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'fubon_head', 'Represent Benefit Head', 'Represent Benefit Heads', 'Đầu Quyền Lợi Đại Diện', 'Đầu Quyền Lợi Đại Diện', 'belong', 1, 1, 0, 0, '', '', 8, 15, 'code', 'code', 'Represent Benefit Head', 'Đầu Quyền Lợi Đại Diện'),
('8f3a406e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_benefit', 'is_combined', 'Is Combined', 'Is Combined', 'Là Kết Hợp', 'Là Kết Hợp', 'checkbox', 0, 0, 0, 3, '', '', 9, 15, 'ben_desc', 'ben_desc', 'Is Combined', 'Là Kết Hợp'),
('8f3a413b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của Thành viên'),
('8f3a43c9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'post', 'metatitle', 'Title', 'Titles', 'Tựa đề', 'Tựa đề', 'text', 1, 0, 0, 200, '', '', 3, 15, '', '', 'Store the sentence that will be shown on the tab bar of the browser and on Google Result page', 'Lưu trữ câu sẽ được hiển thị trên thanh tab của trình duyệt và trên trang Kết quả của Google'),
('8f3ab180-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'mbr_name', 'Member Name', 'Member Names', 'Tên Thành viên', 'Tên Thành viên', 'text', 1, 1, 0, 255, '', '', 2, 3, '', '', 'Name of the Member', 'Tên của Thành viên'),
('8f3ab556-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'mbr_name_2', 'Member Name 2', 'Member Names 2', 'Tên Thành viên 2', 'Tên Thành viên 2', 'text', 1, 1, 0, 255, '', '', 2, 3, '', '', 'Name of the Member 2', 'Tên của Thành viên 2'),
('8f3ab803-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'dob', 'Birth Date', 'Birth Dates', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 1, 0, 0, '', '', 3, 2, '', '', 'Birth Date of the Member', 'Ngày Sinh của Thành viên'),
('8f3ab923-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'pocy_no', 'Policy No', 'Policy Nos', 'Số Hợp đồng', 'Số Hợp đồng', 'text', 1, 1, 0, 20, '', '', 4, 3, '', '', 'Policy No of the Member', 'Số Hợp đồng của Thành viên'),
('8f3aba5c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'pocy_ref_no', 'Policy Ref No', 'Policy Ref Nos', 'Số Tham Chiếu Hợp đồng', 'Số Tham Chiếu Hợp đồng', 'text', 1, 1, 0, 30, '', '', 5, 2, '', '', 'Policy No of the Member', 'Số Tham Chiếu Hợp đồng của Thành viên'),
('8f3abaeb-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'mbr_no', 'Member No', 'Member Nos', 'Số Thành viên', 'Số Thành viên', 'text', 1, 1, 0, 20, '', '', 6, 3, '', '', 'Member No of the Member', 'Số Thành viên của Thành viên'),
('8f3abb6c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'payment_mode', 'Payment Mode', 'Payment Modes', 'Phương Thức Thanh Toán', 'Phương Thức Thanh Toán', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'Payment Mode of the Member', 'Phương Thức Thanh Toán của Thành viên'),
('8f3abbea-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'memb_eff_date', 'Effective Date', 'Effective Dates', 'Ngày Hiệu Lực', 'Ngày Hiệu Lực', 'date', 1, 1, 0, 0, '', '', 8, 3, '', '', 'Effective Date of the Member', 'Ngày Hiệu Lực của Thành viên'),
('8f3abc69-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'memb_exp_date', 'Expiry Date', 'Expiry Dates', 'Ngày Hết Hạn', 'Ngày Hết Hạn', 'date', 1, 1, 0, 0, '', '', 9, 3, '', '', 'Expiry Date of the Member', 'Ngày Hết Hạn của Thành viên'),
('8f3abce5-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'term_date', 'Termination Date', 'Termination Dates', 'Ngày Chấm Dứt', 'Ngày Chấm Dứt', 'date', 1, 1, 0, 0, '', '', 10, 3, '', '', 'Termination Date of the Member', 'Ngày Chấm Dứt của Thành viên'),
('8f3abd68-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'post', 'metadescription', 'Description', 'Descriptions', 'Mô tả', 'Mô tả', 'textarea', 1, 0, 0, 0, '', '', 4, 14, '', '', 'Store the content that will be shown on Google search result', 'Lưu trữ nội dung sẽ được hiển thị trên kết quả tìm kiếm của Google'),
('8f3abdf7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'reinst_date', 'Reinstate Date', 'Reinstate Dates', 'Ngày Tái tục', 'Ngày Ngày Tái tục', 'date', 1, 1, 0, 0, '', '', 11, 2, '', '', 'Reinstate Date of the Member', 'Ngày Ngày Tái tục của Thành viên'),
('8f3abe7f-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'min_memb_eff_date', 'First Member Effective Date', 'First Member Effective Dates', 'Ngày Hiệu Lực Thành Viên Đầu Tiên', 'Ngày Hiệu Lực Thành Viên Đầu Tiên', 'date', 1, 1, 0, 0, '', '', 12, 2, '', '', 'First Member Effective Date of the Member', 'Ngày Hiệu Lực Thành Viên Đầu Tiên của Thành viên'),
('8f3abf0a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'min_pocy_eff_date', 'First Policy Effective Date', 'First Policy Effective Dates', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'date', 1, 1, 0, 0, '', '', 13, 2, '', '', 'First Policy Effective Date of the Member', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên của Thành viên'),
('8f3abf8c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'insured_periods', 'Insurance Periods', 'Insurance Periods', 'Kỳ Bảo hiểm', 'Kỳ Bảo hiểm', 'text', 1, 1, 0, 500, '', '', 14, 2, '', '', 'Insurance Periods of the Member', 'Những Kỳ Bảo hiểm của Thành viên'),
('8f3ac013-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'wait_period', 'Wait Period', 'Wait Periods', 'Thời Kỳ Chờ', 'Thời Kỳ Chờ', 'enum', 1, 1, 0, 0, '', '', 15, 2, '', '', 'Wait Period of the Member', 'Thành viên có Thời Kỳ Chờ không?'),
('8f3ac096-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'spec_dis_period', 'Special Disease Wait Period', 'Special Disease Wait Periods', 'Thời Kỳ Chờ Bệnh Đặc Biệt', 'Thời Kỳ Chờ Bệnh Đặc Biệt', 'enum', 1, 1, 0, 0, '', '', 16, 2, '', '', 'Special Disease Wait Period of the Member', 'Thành viên có Thời Kỳ Chờ Bệnh Đặc Biệt không?'),
('8f3ac116-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'product', 'Product', 'Products', 'Sản Phẩm', 'Sản Phẩm', 'text', 1, 1, 0, 10, '', '', 17, 2, '', '', 'Product of the Member', 'Loại Sản Phẩm của Thành viên'),
('8f3ac195-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'plan_desc', 'Plan Description', 'Plan Descriptions', 'Gói Bảo Hiểm', 'Gói Bảo Hiểm', 'text', 1, 1, 0, 255, '', '', 18, 2, '', '', 'Plan Description of the Member', 'Gói Bảo Hiểm của Thành viên'),
('8f3ac213-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'memb_rstr', 'Restriction', 'Restrictions', 'Loại Trừ', 'Loại Trừ', 'textarea', 1, 1, 0, 9999, '', '', 19, 2, '', '', 'Restrictions of the Member', 'Các Loại Trừ của Thành viên'),
('8f3ac28d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'memb_rstr_vi', 'Restriction (VN)', 'Restrictions (VN)', 'Loại Trừ (VN)', 'Loại Trừ (VN)', 'textarea', 1, 1, 0, 9999, '', '', 20, 2, '', '', 'Restrictions (VN) of the Member', 'Các Loại Trừ (VN) của Thành viên'),
('8f3ac311-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'post', 'metakeyword', 'Keyword', 'Keywords', 'Từ khóa', 'Từ khóa', 'text', 1, 0, 0, 200, '', '', 5, 14, '', '', 'Store the keywords for the search engine to process their search requestd', 'Lưu trữ từ khóa cho công cụ tìm kiếm để xử lý yêu cầu tìm kiếm của họ'),
('8f3ac3a6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'policy_status', 'Member Status', 'Member Statuses', 'Trạng Thái', 'Trạng Thái', 'text', 1, 1, 0, 255, '', '', 21, 3, '', '', 'Member Status of the Member', 'Trạng Thái của Thành viên'),
('8f3ac42a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'is_renew', 'Renew', 'Renew', 'Hợp Đồng Cũ', 'Hợp Đồng Cũ', 'enum', 1, 1, 0, 0, '', '', 22, 3, '', '', 'Renew of the Member', 'Là Thành viên Cũ phải không?'),
('8f3ac4ad-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'op_ind', 'OP Indicator', 'OP Indicators', 'Có Mua Ngoại Trú', 'Có Mua Ngoại Trú', 'enum', 1, 0, 0, 0, '', '', 23, 2, '', '', 'OP Indicator of the current item', 'Thành Viên Có Mua Ngoại Trú không?'),
('8f3ac52d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_member', 'has_op_debit_note', 'Has OP Debit Note', 'Has OP Debit Notes', 'Đã trả tiền OP', 'Đã trả tiền OP', 'enum', 1, 1, 0, 0, '', '', 24, 2, '', '', 'Has OP Debit Note of the Member', 'Thành viên đã trả tiền OP chưa?'),
('8f3ac5b2-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của Bồi Thường'),
('8f3ac646-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'mbr_no', 'Member No', 'Member Nos', 'Số Thành viên', 'Số Thành viên', 'text', 1, 1, 0, 20, '', '', 2, 3, '', '', 'Number of the Member', 'Số của Thành viên'),
('8f3ac6c8-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'dob', 'Birth Date', 'Birth Dates', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 1, 0, 0, '', '', 3, 2, '', '', 'Birth Date of the Member', 'Ngày Sinh của Thành viên'),
('8f3ac748-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'memb_eff_date', 'Effective Date', 'Effective Dates', 'Ngày Hiệu lực', 'Ngày Hiệu lực', 'date', 1, 1, 0, 0, '', '', 4, 2, '', '', 'Effective Date of the Member', 'Ngày Hiệu lực của Thành viên'),
('8f3ac7ca-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'memb_exp_date', 'Expiry Date', 'Expiry Dates', 'Ngày Kết thúc', 'Ngày Kết thúc', 'date', 1, 1, 0, 0, '', '', 5, 2, '', '', 'Expiry Date of the Member', 'Ngày Kết thúc của Thành viên'),
('8f3ac849-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'term_date', 'Termination Date', 'Termination Dates', 'Ngày Chấm dứt', 'Ngày Chấm dứt', 'date', 1, 1, 0, 0, '', '', 6, 2, '', '', 'Termination Date of the Member', 'Ngày Chấm dứt của Thành viên'),
('8f3ac8c6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'post', 'content', 'Content', 'Contents', 'Nội dung', 'Nội dung', 'html', 1, 0, 0, 0, '', '', 6, 14, '', '', 'Store the content of this page if this is a static page', 'Lưu trữ nội dung của trang này nếu đây là trang tĩnh'),
('8f3ac946-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'cl_no', 'Claim No', 'Claim Nos', 'Số Bồi Thường', 'Số Bồi Thường', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'Claim No of the claim', 'Số Bồi Thường của Bồi Thường'),
('8f3ac9d9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'db_ref_no', 'DB Ref No', 'DB Ref Nos', 'Số Tham Chiếu Bồi Thường', 'Số Tham Chiếu Bồi Thường', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'DB Ref No of the claim', 'Số Tham Chiếu Bồi Thường của Bồi Thường'),
('8f3aca5b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'incur_date_from', 'Incur Date From', 'Incur Date Froms', 'Ngày Nhập viện', 'Ngày Nhập viện', 'date', 1, 1, 0, 0, '', '', 8, 3, '', '', 'Incur Date From of the claim', 'Ngày Nhập viện của Bồi Thường'),
('8f3acb8f-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'ben_head', 'Benefit Head', 'Benefit Heads', 'Mã Quyền Lợi', 'Mã Quyền Lợi', 'text', 1, 1, 0, 10, '', '', 9, 2, '', '', 'Benefit Head of the claim', 'Mã Quyền Lợi của Bồi Thường'),
('8f3acc0b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'ben_type', 'Benefit Type', 'Benefit Types', 'Loại Quyền Lợi', 'Loại Quyền Lợi', 'text', 1, 1, 0, 10, '', '', 10, 2, '', '', 'Benefit Type of the claim', 'Loại Quyền Lợi của Bồi Thường'),
('8f3acc84-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'status', 'Status', 'Statuses', 'Trạng Thái', 'Trạng Thái', 'text', 1, 1, 0, 11, '', '', 10, 2, '', '', 'Status of the claim', 'Trạng Thái của Bồi Thường'),
('8f3acd02-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'diag_code', 'Diagnosis Code', 'Diagnosis Codes', 'Mã Chẩn Đoán', 'Mã Chẩn Đoán', 'text', 0, 1, 0, 255, '', '', 12, 2, '', '', 'Diagnosis Code of the claim', 'Mã Chẩn Đoán của Bồi Thường'),
('8f3acd80-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'prov_code', 'Provider Code', 'Provider Codes', 'Mã Nhà Cung Cấp', 'Mã Nhà Cung Cấp', 'text', 0, 1, 0, 255, '', '', 13, 2, '', '', 'Provider Code of the claim', 'Mã Nhà Cung Cấp của Bồi Thường'),
('8f3acdfb-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'prov_name', 'Provider Name', 'Provider Names', 'Tên Nhà Cung Cấp', 'Tên Nhà Cung Cấp', 'text', 0, 1, 0, 255, '', '', 14, 2, '', '', 'Provider Name of the claim', 'Tên Nhà Cung Cấp của Bồi Thường'),
('8f3ace8b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'pres_amt', 'Presented Amount', 'Presented Amount', 'Số tiền Yêu Cầu', 'Số tiền Yêu Cầu', 'double', 1, 0, 0, 0, '', '', 15, 2, '', '', 'Presented Amount of the claim', 'Số tiền Yêu cầu của Bồi Thường'),
('8f3ad060-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'post', 'enabled', 'Enabled', 'Enableds', 'Kích hoạt', 'Kích hoạt', 'checkbox', 0, 0, 0, 1, '', '', 7, 15, '', '', 'Specify if this page is accessible or not', 'Chỉ định xem trang này có thể truy cập được hay không'),
('8f3ad0e9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_claim_line', 'app_amt', 'Approved Amount', 'Approved Amount', 'Số tiền Chấp Nhận', 'Số tiền Chấp Nhận', 'double', 1, 0, 0, 0, '', '', 16, 2, '', '', 'Approved Amount of the claim', 'Số tiền Chấp Nhận của Bồi Thường'),
('8f3ad16b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 0, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của Khách Hàng'),
('8f3ad1fd-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'poho_no', 'Policyholder No', 'Policyholder Nos', 'Mã Chủ Hợp đồng', 'Số Hợp đồng', 'text', 1, 0, 0, 0, '', '', 2, 3, '', '', 'Policyholder No of the Client', 'Mã Chủ Hợp đồng của Khách Hàng'),
('8f3ad33e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'poho_name', 'Policyholder Name', 'Policyholder Names', 'Tên Chủ Hợp đồng', 'Tên Chủ Hợp đồng', 'text', 1, 0, 0, 0, '', '', 3, 3, '', '', 'Policyholder Name of the Client', 'Tên Chủ Hợp đồng của Khách Hàng'),
('8f3ad3c8-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'tax_code', 'Tax Code', 'Tax Codes', 'Mã Số Thuế', 'Mã Số Thuế', 'text', 0, 0, 0, 0, '', '', 4, 3, '', '', 'Tax Code of the Client', 'Mã Số Thuế của Khách Hàng'),
('8f3ad451-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'pocy_type', 'Policy Type', 'Policy Types', 'Loại Hợp đồng', 'Loại Hợp đồng', 'text', 1, 0, 0, 0, '', '', 5, 3, '', '', 'Policy Type of the Client', 'Loại Hợp đồng của Khách Hàng'),
('8f3ad4de-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'pocy_no', 'Policy No', 'Policy Nos', 'Mã Hợp đồng', 'Mã Hợp đồng', 'text', 1, 0, 0, 0, '', '', 6, 3, '', '', 'Policy No of the Client', 'Mã Hợp đồng của Khách Hàng'),
('8f3ad64b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'pre_pocy_no', 'Previous Policy No', 'Previous Policy Nos', 'Mã Tiền Hợp đồng', 'Mã Tiền Hợp đồng', 'text', 1, 0, 0, 0, '', '', 7, 3, '', '', 'Previous Policy No of the Client', 'Mã Tiền Hợp đồng của Khách Hàng'),
('8f3ad6c9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'min_pocy_no', 'First Policy No', 'First Policy Nos', 'Mã Tiên Hợp đồng', 'Mã Tiên Hợp đồng', 'text', 1, 0, 0, 0, '', '', 8, 3, '', '', 'First Policy No of the Client', 'Mã Tiên Hợp đồng của Khách Hàng'),
('8f3ad747-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'min_pocy_eff_date', 'First Policy Effective Date', 'First Policy Effective Dates', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'text', 1, 0, 0, 0, '', '', 9, 3, '', '', 'First Policy Effective Date of the Client', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên của Khách Hàng'),
('8f3ad87f-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'username', 'Username', 'Usernames', 'Tên đăng nhập', 'Tên đăng nhập', 'text', 1, 1, 0, 50, '', '', 2, 15, '', '', 'Username of the User', 'Tên đăng nhập của người dùng'),
('8f3af35f-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'provider', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3af435-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'pocy_status', 'Policy Status', 'Policy Statuses', 'Trạng Thái Hợp Đồng', 'Trạng Thái Hợp Đồng', 'text', 1, 0, 0, 0, '', '', 10, 3, '', '', 'Policy Status of the Client', 'Trạng Thái Hợp Đồng của Khách Hàng'),
('8f3af4d0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'pocy_eff_date', 'Policy Effective Date', 'Policy Effective Dates', 'Ngày Hiệu Lực Hợp đồng', 'Ngày Hiệu Lực Hợp đồng', 'text', 1, 0, 0, 0, '', '', 11, 3, '', '', 'Policy Effective Date of the Client', 'Ngày Hiệu Lực Hợp đồng của Khách Hàng'),
('8f3af555-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'pocy_exp_date', 'Policy Expiry Date', 'Policy Expiry Dates', 'Ngày Kết Thúc Hợp đồng', 'Ngày Kết Thúc Hợp đồng', 'text', 1, 0, 0, 0, '', '', 12, 3, '', '', 'Policy Expiry Date of the Client', 'Ngày Kết thúc Hợp đồng của Khách Hàng'),
('8f3af5d5-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'payment_mode', 'Payment Mode', 'Payment Modes', 'Phương Thức Thanh Toán', 'Phương Thức Thanh Toán', 'text', 0, 0, 0, 0, '', '', 13, 3, '', '', 'Payment Mode of the Client', 'Phương Thức Thanh Toán của Khách Hàng'),
('8f3af657-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'prem_amt', 'Total Premium', 'Total Premiums', 'Tổng Phí của Khách Hàng', 'Tổng Phí của Khách Hàng', 'double', 1, 0, 0, 0, '', '', 14, 3, '', '', 'Total Premium of the Client', 'Tổng Phí của Khách Hàng'),
('8f3af6da-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'mbr_name', 'Client Name', 'Client Names', 'Tên Khách Hàng', 'Tên Khách Hàng', 'text', 1, 0, 0, 0, '', '', 15, 3, '', '', 'Name of the Client', 'Tên của Khách Hàng'),
('8f3af759-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'memb_status', 'Client Status', 'Client Statuses', 'Trạng Thái Khách Hàng', 'Trạng Thái Khách Hàng', 'text', 1, 0, 0, 0, '', '', 16, 3, '', '', 'Status of the Client', 'Trạng Thái của Khách Hàng'),
('8f3af7d5-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'memb_eff_date', 'Client Effective Date', 'Client Effective Dates', 'Ngày Hiệu Lực', 'Ngày Hiệu Lực', 'text', 1, 0, 0, 0, '', '', 17, 3, '', '', 'Effective Date of the Client', 'Ngày Hiệu Lực của Khách Hàng'),
('8f3af854-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'memb_exp_date', 'Client Expiry Date', 'Client Expiry Dates', 'Ngày Hết Hạn', 'Ngày Hết Hạn', 'text', 1, 0, 0, 0, '', '', 18, 3, '', '', 'Expiry Date of the Client', 'Ngày Hết Hạn của Khách Hàng'),
('8f3af8d2-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'gender', 'Gender', 'Genders', 'Giới Tính', 'Giới Tính', 'text', 1, 0, 0, 0, '', '', 19, 3, '', '', 'Gender of the Client', 'Giới Tính của Khách Hàng'),
('8f3af957-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'provider', 'code', 'Code', 'Codes', 'Mã', 'Mã', 'text', 1, 1, 0, 200, '', '', 2, 15, '', '', 'The Code of the curent item', 'Mã của mục này'),
('8f3af9dc-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'dob', 'Birth Date', 'Birth Dates', 'Ngày Sinh', 'Ngày Sinh', 'text', 1, 0, 0, 0, '', '', 20, 3, '', '', 'Birth Date of the Client', 'Ngày Sinh của Khách Hàng'),
('8f3afa5a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'id_card_no', 'ID/Card No', 'ID/Card Nos', 'Số CMND/Hộ Chiếu của Khách Hàng', 'Số CMND/Hộ Chiếu của Khách Hàng', 'text', 1, 0, 0, 21, '', '', 20, 3, '', '', 'ID/Card No of the Client', 'Số CMND/Hộ Chiếu của Khách Hàng'),
('8f3afadc-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'citizen', 'Country of Citizenship', 'Countries of Citizenship', 'Nước Cư Trú', 'Nước Cư Trú', 'text', 0, 0, 0, 0, '', '', 22, 3, '', '', 'Country of Citizenship of the Client', 'Nước Cư Trú của Khách Hàng'),
('8f3afb5b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'phone', 'Telephone', 'Telephones', 'Số Điện Thoại', 'Số Điện Thoại', 'text', 0, 0, 0, 0, '', '', 23, 3, '', '', 'Telephone of the Client', 'Số Điện Thoại của Khách Hàng'),
('8f3afbd7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'email', 'Email', 'Emails', 'Thư Điện Tử', 'Thư Điện Tử', 'text', 0, 0, 0, 0, '', '', 24, 3, '', '', 'Email of the Client', 'Thư Điện Tử của Khách Hàng'),
('8f3afc52-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'address', 'Address', 'Addresses', 'Địa Chỉ', 'Địa Chỉ', 'text', 0, 0, 0, 0, '', '', 25, 3, '', '', 'Address of the Client', 'Địa Chỉ của Khách Hàng'),
('8f3afccc-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'plan', 'Plan', 'Plan', 'Gói Bảo Hiểm', 'Gói Bảo Hiểm', 'text', 1, 0, 0, 0, '', '', 26, 3, '', '', 'Plan of the Client', 'Gói Bảo Hiểm của Khách Hàng'),
('8f3afd48-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'sum_insured', 'Sum Insured', 'Sum Insureds', 'Tổng Mức Bảo Hiểm', 'Tổng Mức Bảo Hiểm', 'double', 1, 0, 0, 0, '', '', 27, 3, '', '', 'Restrictions of the Client', 'Tổng Mức Bảo Hiểm của Khách Hàng'),
('8f3afdc5-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'ben_1', 'Benefit 1', 'Benefits 1', 'Quyền Lợi 1', 'Quyền Lợi 1', 'double', 0, 0, 0, 0, '', '', 28, 3, '', '', 'Benefit 1 of the Client', 'Quyền Lợi 1 của Khách Hàng'),
('8f3afe42-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'ben_2', 'Benefit 2', 'Benefits 2', 'Quyền Lợi 2', 'Quyền Lợi 2', 'double', 0, 0, 0, 0, '', '', 29, 3, '', '', 'Benefit 2 of the Client', 'Quyền Lợi 2 của Khách Hàng'),
('8f3afebe-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'provider', 'name', 'Name', 'Names', 'Tên', 'Tên', 'text', 1, 1, 0, 200, '', '', 3, 15, '', '', 'The Name of the curent item', 'Tên của mục này'),
('8f3aff43-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'ben_3', 'Benefit 3', 'Benefits 3', 'Quyền Lợi 3', 'Quyền Lợi 3', 'double', 0, 0, 0, 0, '', '', 30, 3, '', '', 'Benefit 3 of the Client', 'Quyền Lợi 3 của Khách Hàng'),
('8f3affc0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'ben_4', 'Benefit 4', 'Benefits 4', 'Quyền Lợi 4', 'Quyền Lợi 4', 'double', 0, 0, 0, 0, '', '', 31, 3, '', '', 'Benefit 4 of the Client', 'Quyền Lợi 4 của Khách Hàng'),
('8f3b0039-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'ben_5', 'Benefit 5', 'Benefits 5', 'Quyền Lợi 5', 'Quyền Lợi 5', 'double', 0, 0, 0, 0, '', '', 32, 3, '', '', 'Benefit 5 of the Client', 'Quyền Lợi 5 của Khách Hàng'),
('8f3b00b0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'ben_6', 'Benefit 6', 'Benefits 6', 'Quyền Lợi 6', 'Quyền Lợi 6', 'double', 0, 0, 0, 0, '', '', 33, 3, '', '', 'Benefit 6 of the Client', 'Quyền Lợi 6 của Khách Hàng'),
('8f3b0128-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_client', 'exclusion', 'Exclusions', 'Exclusions', 'Loại Trừ', 'Loại Trừ', 'text', 0, 0, 0, 0, '', '', 34, 3, '', '', 'Exclusions of the Client', 'Các Loại Trừ của Khách Hàng'),
('8f3b01a3-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_head', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3b0234-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_head', 'code', 'Code', 'Codes', 'Mã', 'Mã', 'text', 1, 1, 0, 0, '', '', 2, 15, '', '', 'Code', 'Mã'),
('8f3b02c5-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_head', 'ben_heads', 'Ben Heads', 'Ben Heads', 'Quyền Lợi', 'Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Ben Heads', 'Quyền Lợi'),
('8f3b034b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_head', 'name', 'Name', 'Names', 'Tên', 'Tên', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Name', 'Tên'),
('8f3b03cd-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_head', 'name_vi', 'Name (VN)', 'Names (VN)', 'Tên (VN)', 'Tên (VN)', 'text', 1, 1, 0, 0, '', '', 5, 15, '', '', 'Name (VN)', 'Tên (VN)'),
('8f3b044f-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3b04d9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_head', 'cathay_benefit', 'Primary Benefit', 'Primary Benefits', 'Quyền Lợi Chính', 'Quyền Lợi Chính', 'belong', 1, 0, 0, 0, '', '', 6, 15, 'ben_desc', 'ben_desc', 'Primary Benefit', 'Quyền Lợi Chính'),
('8f3b0556-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 3, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3b05e4-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'parent', 'Parent', 'Parents', 'Mục Cha', 'Mục Cha', 'self', 0, 0, 0, 0, '', '', 2, 15, '', 'ben_desc', 'Parent', 'Mục Cha'),
('8f3b0669-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'ben_type', 'Benefit Type', 'Benefit Types', 'Loại Quyền Lợi', 'Loại Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Benefit Type', 'Loại Quyền Lợi'),
('8f3b06ee-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'ben_desc', 'Benefit Description', 'Benefit Descriptions', 'Mô Tả Quyền Lợi', 'Mô Tả Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Benefit Description', 'Mô Tả Quyền Lợi'),
('8f3b0771-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'ben_desc_vi', 'Benefit Description (VN)', 'Benefit Descriptions (VN)', 'Mô Tả Quyền Lợi (VN)', 'Mô Tả Quyền Lợi (VN)', 'text', 1, 1, 0, 0, '', '', 5, 15, '', '', 'Benefit Description (VN)', 'Mô Tả Quyền Lợi (VN)'),
('8f3b07f6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'ben_note', 'Benefit Note', 'Benefit Notes', 'Ghi Chú Quyền Lợi', 'Ghi Chú Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 6, 15, '', '', 'Benefit Note', 'Ghi Chú Quyền Lợi'),
('8f3b087d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'ben_note_vi', 'Benefit Note (VN)', 'Benefit Notes (VN)', 'Ghi Chú Quyền Lợi (VN)', 'Ghi Chú Quyền Lợi (VN)', 'text', 1, 1, 0, 0, '', '', 7, 15, '', '', 'Benefit Note (VN)', 'Ghi Chú Quyền Lợi (VN)'),
('8f3b08fa-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'cathay_head', 'Represent Benefit Head', 'Represent Benefit Heads', 'Đầu Quyền Lợi Đại Diện', 'Đầu Quyền Lợi Đại Diện', 'belong', 1, 1, 0, 0, '', '', 8, 15, 'code', 'code', 'Represent Benefit Head', 'Đầu Quyền Lợi Đại Diện'),
('8f3b09b1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_benefit', 'is_combined', 'Is Combined', 'Is Combined', 'Là Kết Hợp', 'Là Kết Hợp', 'checkbox', 0, 0, 0, 3, '', '', 9, 15, 'ben_desc', 'ben_desc', 'Is Combined', 'Là Kết Hợp'),
('8f3b0a37-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'pocy_no', 'Policy No', 'Policy Nos', 'Mã Hợp Đồng', 'Mã Hợp Đồng', 'text', 1, 1, 0, 50, '', '', 2, 15, '', '', 'Policy No of the Mobile user', 'Mã Hợp Đồng của Người dùng Di Động'),
('8f3b0aba-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của Thành viên'),
('8f3b0b49-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'mbr_name', 'Member Name', 'Member Names', 'Tên Thành viên', 'Tên Thành viên', 'text', 1, 1, 0, 255, '', '', 2, 3, '', '', 'Name of the Member', 'Tên của Thành viên'),
('8f3b0bcc-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'dob', 'Birth Date', 'Birth Dates', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 1, 0, 0, '', '', 3, 2, '', '', 'Birth Date of the Member', 'Ngày Sinh của Thành viên'),
('8f3b0c4e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'pocy_no', 'Policy No', 'Policy Nos', 'Số Hợp đồng', 'Số Hợp đồng', 'text', 1, 1, 0, 20, '', '', 4, 3, '', '', 'Policy No of the Member', 'Số Hợp đồng của Thành viên'),
('8f3b0cd0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'pocy_ref_no', 'Policy Ref No', 'Policy Ref Nos', 'Số Tham Chiếu Hợp đồng', 'Số Tham Chiếu Hợp đồng', 'text', 1, 1, 0, 30, '', '', 5, 2, '', '', 'Policy No of the Member', 'Số Tham Chiếu Hợp đồng của Thành viên'),
('8f3b0d59-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'mbr_no', 'Member No', 'Member Nos', 'Số Thành viên', 'Số Thành viên', 'text', 1, 1, 0, 20, '', '', 6, 3, '', '', 'Member No of the Member', 'Số Thành viên của Thành viên'),
('8f3b0dd7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'memb_ref_no', 'Member Ref No', 'Member Ref Nos', 'Số Tham Chiếu Thành viên', 'Số Tham Chiếu Thành viên', 'text', 1, 1, 0, 20, '', '', 6, 3, '', '', 'Member No of the Member', 'Số Tham Chiếu Thành viên của Thành viên'),
('8f3b0e53-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'payment_mode', 'Payment Mode', 'Payment Modes', 'Phương Thức Thanh Toán', 'Phương Thức Thanh Toán', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'Payment Mode of the Member', 'Phương Thức Thanh Toán của Thành viên'),
('8f3b0ed0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'memb_eff_date', 'Effective Date', 'Effective Dates', 'Ngày Hiệu Lực', 'Ngày Hiệu Lực', 'date', 1, 1, 0, 0, '', '', 8, 3, '', '', 'Effective Date of the Member', 'Ngày Hiệu Lực của Thành viên'),
('8f3b0f4a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'memb_exp_date', 'Expiry Date', 'Expiry Dates', 'Ngày Hết Hạn', 'Ngày Hết Hạn', 'date', 1, 1, 0, 0, '', '', 9, 3, '', '', 'Expiry Date of the Member', 'Ngày Hết Hạn của Thành viên'),
('8f3b0fc5-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'mbr_no', 'Username', 'Usernames', 'Tên đăng nhập', 'Tên đăng nhập', 'text', 1, 1, 0, 50, '', '', 3, 15, '', '', 'Member No of the Mobile user', 'Mã Người Dùng của Người dùng Di Động'),
('8f3b1049-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'term_date', 'Termination Date', 'Termination Dates', 'Ngày Chấm Dứt', 'Ngày Chấm Dứt', 'date', 1, 1, 0, 0, '', '', 10, 3, '', '', 'Termination Date of the Member', 'Ngày Chấm Dứt của Thành viên'),
('8f3b10c9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'reinst_date', 'Reinstate Date', 'Reinstate Dates', 'Ngày Tái tục', 'Ngày Ngày Tái tục', 'date', 1, 1, 0, 0, '', '', 11, 2, '', '', 'Reinstate Date of the Member', 'Ngày Ngày Tái tục của Thành viên'),
('8f3b1147-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'min_memb_eff_date', 'First Member Effective Date', 'First Member Effective Dates', 'Ngày Hiệu Lực Thành Viên Đầu Tiên', 'Ngày Hiệu Lực Thành Viên Đầu Tiên', 'date', 1, 1, 0, 0, '', '', 12, 2, '', '', 'First Member Effective Date of the Member', 'Ngày Hiệu Lực Thành Viên Đầu Tiên của Thành viên'),
('8f3b11c7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'min_pocy_eff_date', 'First Policy Effective Date', 'First Policy Effective Dates', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'date', 1, 1, 0, 0, '', '', 13, 2, '', '', 'First Policy Effective Date of the Member', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên của Thành viên'),
('8f3b1243-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'insured_periods', 'Insurance Periods', 'Insurance Periods', 'Kỳ Bảo hiểm', 'Kỳ Bảo hiểm', 'text', 1, 1, 0, 500, '', '', 14, 2, '', '', 'Insurance Periods of the Member', 'Những Kỳ Bảo hiểm của Thành viên'),
('8f3b12c4-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'wait_period', 'Wait Period', 'Wait Periods', 'Thời Kỳ Chờ', 'Thời Kỳ Chờ', 'enum', 1, 1, 0, 0, '', '', 15, 2, '', '', 'Wait Period of the Member', 'Thành viên có Thời Kỳ Chờ không?'),
('8f3b133d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'spec_dis_period', 'Special Disease Wait Period', 'Special Disease Wait Periods', 'Thời Kỳ Chờ Bệnh Đặc Biệt', 'Thời Kỳ Chờ Bệnh Đặc Biệt', 'enum', 1, 1, 0, 0, '', '', 16, 2, '', '', 'Special Disease Wait Period of the Member', 'Thành viên có Thời Kỳ Chờ Bệnh Đặc Biệt không?'),
('8f3b13bb-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'product', 'Product', 'Products', 'Sản Phẩm', 'Sản Phẩm', 'text', 1, 1, 0, 10, '', '', 17, 2, '', '', 'Product of the Member', 'Loại Sản Phẩm của Thành viên'),
('8f3b33e1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'plan_desc', 'Plan Description', 'Plan Descriptions', 'Gói Bảo Hiểm', 'Gói Bảo Hiểm', 'text', 1, 1, 0, 255, '', '', 18, 2, '', '', 'Plan Description of the Member', 'Gói Bảo Hiểm của Thành viên'),
('8f3b350a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'memb_rstr', 'Restriction', 'Restrictions', 'Loại Trừ', 'Loại Trừ', 'textarea', 1, 1, 0, 9999, '', '', 19, 2, '', '', 'Restrictions of the Member', 'Các Loại Trừ của Thành viên'),
('8f3b35ab-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'password', 'Password', 'Passwords', 'Mật khẩu', 'Mật Khẩu', 'password', 1, 0, 0, 50, '', 'invalid_password', 4, 12, '', '', 'Password of the Mobile user', 'Mật khẩu của Người dùng Di Động'),
('8f3b3647-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'memb_rstr_vi', 'Restriction (VN)', 'Restrictions (VN)', 'Loại Trừ (VN)', 'Loại Trừ (VN)', 'textarea', 1, 1, 0, 9999, '', '', 20, 2, '', '', 'Restrictions (VN) of the Member', 'Các Loại Trừ (VN) của Thành viên'),
('8f3b36d6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'policy_status', 'Member Status', 'Member Statuses', 'Trạng Thái', 'Trạng Thái', 'text', 1, 1, 0, 255, '', '', 21, 3, '', '', 'Member Status of the Member', 'Trạng Thái của Thành viên'),
('8f3b3759-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'is_renew', 'Renew', 'Renew', 'Hợp Đồng Cũ', 'Hợp Đồng Cũ', 'enum', 1, 1, 0, 0, '', '', 22, 3, '', '', 'Renew of the Member', 'Là Thành viên Cũ phải không?'),
('8f3b37dd-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'op_ind', 'OP Indicator', 'OP Indicators', 'Có Mua Ngoại Trú', 'Có Mua Ngoại Trú', 'enum', 1, 0, 0, 0, '', '', 23, 2, '', '', 'OP Indicator of the current item', 'Thành Viên Có Mua Ngoại Trú không?'),
('8f3b385b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_member', 'has_op_debit_note', 'Has OP Debit Note', 'Has OP Debit Notes', 'Đã trả tiền OP', 'Đã trả tiền OP', 'enum', 1, 1, 0, 0, '', '', 24, 2, '', '', 'Has OP Debit Note of the Member', 'Thành viên đã trả tiền OP chưa?'),
('8f3b3987-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của Bồi Thường'),
('8f3b3a50-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'mbr_no', 'Member No', 'Member Nos', 'Số Thành viên', 'Số Thành viên', 'text', 1, 1, 0, 20, '', '', 2, 3, '', '', 'Number of the Member', 'Số của Thành viên'),
('8f3b3af0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'dob', 'Birth Date', 'Birth Dates', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 1, 0, 0, '', '', 3, 2, '', '', 'Birth Date of the Member', 'Ngày Sinh của Thành viên'),
('8f3b3b8d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'memb_eff_date', 'Effective Date', 'Effective Dates', 'Ngày Hiệu lực', 'Ngày Hiệu lực', 'date', 1, 1, 0, 0, '', '', 4, 2, '', '', 'Effective Date of the Member', 'Ngày Hiệu lực của Thành viên');
INSERT INTO `lzafield` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzamodule_id`, `field`, `single`, `plural`, `single_vi`, `plural_vi`, `type`, `mandatory`, `is_unique`, `minlength`, `maxlength`, `regex`, `error`, `order_by`, `level`, `statistic`, `display`, `note`, `note_vi`) VALUES
('8f3b3c2d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'memb_exp_date', 'Expiry Date', 'Expiry Dates', 'Ngày Kết thúc', 'Ngày Kết thúc', 'date', 1, 1, 0, 0, '', '', 5, 2, '', '', 'Expiry Date of the Member', 'Ngày Kết thúc của Thành viên'),
('8f3b3cd1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'fullname', 'Full Name', 'Full Names', 'Họ Tên', 'Họ Tên', 'text', 1, 0, 0, 50, '', '', 5, 15, '', '', 'Full Name of the Mobile user', 'Họ Tên của Người dùng Di Động'),
('8f3b3d5d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'term_date', 'Termination Date', 'Termination Dates', 'Ngày Chấm dứt', 'Ngày Chấm dứt', 'date', 1, 1, 0, 0, '', '', 6, 2, '', '', 'Termination Date of the Member', 'Ngày Chấm dứt của Thành viên'),
('8f3b3df7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'cl_no', 'Claim No', 'Claim Nos', 'Số Bồi Thường', 'Số Bồi Thường', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'Claim No of the claim', 'Số Bồi Thường của Bồi Thường'),
('8f3b3e8e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'db_ref_no', 'DB Ref No', 'DB Ref Nos', 'Số Tham Chiếu Bồi Thường', 'Số Tham Chiếu Bồi Thường', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'DB Ref No of the claim', 'Số Tham Chiếu Bồi Thường của Bồi Thường'),
('8f3b60b1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'incur_date_from', 'Incur Date From', 'Incur Date Froms', 'Ngày Nhập viện', 'Ngày Nhập viện', 'date', 1, 1, 0, 0, '', '', 8, 3, '', '', 'Incur Date From of the claim', 'Ngày Nhập viện của Bồi Thường'),
('8f3b622f-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'ben_head', 'Benefit Head', 'Benefit Heads', 'Mã Quyền Lợi', 'Mã Quyền Lợi', 'text', 1, 1, 0, 10, '', '', 9, 2, '', '', 'Benefit Head of the claim', 'Mã Quyền Lợi của Bồi Thường'),
('8f3b62d6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'ben_type', 'Benefit Type', 'Benefit Types', 'Loại Quyền Lợi', 'Loại Quyền Lợi', 'text', 1, 1, 0, 10, '', '', 10, 2, '', '', 'Benefit Type of the claim', 'Loại Quyền Lợi của Bồi Thường'),
('8f3b6372-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'status', 'Status', 'Statuses', 'Trạng Thái', 'Trạng Thái', 'text', 1, 1, 0, 11, '', '', 10, 2, '', '', 'Status of the claim', 'Trạng Thái của Bồi Thường'),
('8f3b6407-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'diag_code', 'Diagnosis Code', 'Diagnosis Codes', 'Mã Chẩn Đoán', 'Mã Chẩn Đoán', 'text', 0, 1, 0, 255, '', '', 12, 2, '', '', 'Diagnosis Code of the claim', 'Mã Chẩn Đoán của Bồi Thường'),
('8f3b648e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'prov_code', 'Provider Code', 'Provider Codes', 'Mã Nhà Cung Cấp', 'Mã Nhà Cung Cấp', 'text', 0, 1, 0, 255, '', '', 13, 2, '', '', 'Provider Code of the claim', 'Mã Nhà Cung Cấp của Bồi Thường'),
('8f3b6512-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'prov_name', 'Provider Name', 'Provider Names', 'Tên Nhà Cung Cấp', 'Tên Nhà Cung Cấp', 'text', 0, 1, 0, 255, '', '', 14, 2, '', '', 'Provider Name of the claim', 'Tên Nhà Cung Cấp của Bồi Thường'),
('8f3b6591-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'address', 'Address', 'Addresses', 'Địa Chỉ', 'Địa Chỉ', 'text', 1, 0, 0, 200, '', '', 6, 15, '', '', 'Address of the Mobile user', 'Địa Chỉ của Người dùng Di Động'),
('8f3b662c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'pres_amt', 'Presented Amount', 'Presented Amount', 'Số tiền Yêu Cầu', 'Số tiền Yêu Cầu', 'double', 1, 0, 0, 0, '', '', 15, 2, '', '', 'Presented Amount of the claim', 'Số tiền Yêu cầu của Bồi Thường'),
('8f3b66b3-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_claim_line', 'app_amt', 'Approved Amount', 'Approved Amount', 'Số tiền Chấp Nhận', 'Số tiền Chấp Nhận', 'double', 1, 0, 0, 0, '', '', 16, 2, '', '', 'Approved Amount of the claim', 'Số tiền Chấp Nhận của Bồi Thường'),
('8f3b673c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'photo', 'Photo', 'Photos', 'Hình', 'Hình', 'json', 1, 0, 0, 0, '', '', 7, 14, '', '', 'Photo of the Mobile user', 'Hình của Người dùng Di Động'),
('8f3b6853-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'password', 'Password', 'Passwords', 'Mật khẩu', 'Mật Khẩu', 'password', 1, 0, 0, 50, '', 'invalid_password', 3, 12, '', '', 'Password of the User', 'Mật khẩu của người dùng'),
('8f3b68ea-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'tel', 'Telephone', 'Telephones', 'Số Điện Thoại', 'Số Điện Thoại', 'text', 1, 0, 0, 50, '', '', 8, 15, '', '', 'Telephone of the Mobile user', 'Số Điện Thoại của Người dùng Di Động'),
('8f3b6978-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'email', 'Email', 'Emails', 'Thư điện tử', 'Thư điện tử', 'email', 1, 1, 0, 200, '', '', 9, 15, '', '', 'Email Address of the Mobile user', 'Thư điện tử của Người dùng Di Động'),
('8f3b6a02-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'language', 'Language', 'Languages', 'Ngôn Ngữ', 'Ngôn Ngữ', 'enum', 1, 1, 0, 200, '', '', 10, 15, '', '', 'Language of the Mobile user', 'Ngôn Ngữ của Người dùng Di Động'),
('8f3b6a91-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user', 'enabled', 'Enabled', 'Enabled', 'Kích hoạt', 'Kích hoạt', 'checkbox', 0, 0, 0, 1, '', '', 11, 14, '', '[\"Yes\",\"No\"]', 'Specify will this Mobile user is enabled or not', 'Xác định Người dùng Di Động này có được kích hoạt hay không'),
('8f3b6b21-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Mantis Id number of the current item', 'Mã Mantis của mục này'),
('8f3b6bb8-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'mantis_id', 'Mantis Id', 'Mantis Ids', 'Mã số Mantis', 'Mã số Mantis', 'integer', 1, 0, 0, 11, '', '', 2, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3b6c40-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'mobile_user', 'Mobile User', 'Mobile Users', 'Người dùng Di Động', 'Người dùng Di Động', 'belong', 1, 1, 0, 50, '', '', 3, 15, 'mbr_no', 'mbr_no', 'Mobile User of the Mobile claim', 'Người dùng di động của Bồi thường Di Động'),
('8f3b6cce-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'pay_type', 'Payment Type', 'Payment Types', 'Kiểu Thanh Toán', 'Kiểu Thanh Toán', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Payment Type of the Mobile Claim', 'Kiểu Thanh Toán của Bồi Thường Di Động'),
('8f3b6d5e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'mobile_user_bank_account', 'Bank Account', 'Bank Accounts', 'Tài Khoản', 'Tài Khoản', 'belong', 0, 1, 0, 50, '', '', 5, 15, 'mbr_no', 'mbr_no', 'Bank Account of the Mobile claim', 'Tài Khoản của Bồi thường Di Động'),
('8f3b6deb-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'mobile_claim_status', 'Status', 'Statuses', 'Trạng Thái', 'Trạng Thái', 'belong', 1, 0, 0, 0, '', '', 6, 15, 'name', 'name', 'Status of the Mobile Claim', 'Trạng Thái của Bồi Thường Di Động'),
('8f3b6e70-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'fullname', 'Full Name', 'Full Names', 'Họ Tên', 'Họ Tên', 'text', 1, 0, 0, 50, '', '', 4, 15, '', '', 'Full Name of the User', 'Họ Tên của người dùng'),
('8f3b6f04-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'reason', 'Reason', 'Reasons', 'Lý Do Xảy Ra', 'Lý Do Xảy Ra', 'text', 0, 1, 0, 0, '', '', 7, 14, '', '', 'Result Of of the Mobile Claim', 'Lý Do Xảy Ra Của Bồi Thường Di Động'),
('8f3b6f8d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'symtom_time', 'Symtom Time', 'Symtom Times', 'Thời Điểm Xảy Ra Triệu Chứng', 'Thời Điểm Xảy Ra Triệu Chứng', 'datetime', 0, 1, 0, 0, '', '', 8, 15, '', '', 'Symtom Time of the Mobile Claim', 'Thời Điểm Xảy Ra Triệu Chứng của Bồi Thường Di Động'),
('8f3b701e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'occur_time', 'Occur Time', 'Occur Times', 'Thời Điểm Xảy Ra', 'Thời Điểm Xảy Ra', 'datetime', 0, 1, 0, 0, '', '', 9, 15, '', '', 'Occur Time of the Mobile Claim', 'Thời Điểm Xảy Ra của Bồi Thường Di Động'),
('8f3b70a6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'body_part', 'Body Part', 'Body Parts', 'Bộ Phận Cơ Thể', 'Bộ Phận Cơ Thể', 'text', 0, 1, 0, 0, '', '', 10, 14, '', '', 'Body Part of the Mobile Claim', 'Bộ Phận Cơ Thể của Bồi Thường Di Động'),
('8f3b712e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'incident_detail', 'Incident Detail', 'Incident Details', 'Chi Tiết Tai Nạn', 'Chi Tiết Tai Nạn', 'text', 0, 1, 0, 0, '', '', 11, 14, '', '', 'Incident Detail of the Mobile Claim', 'Chi Tiết Tai Nạn của Bồi Thường Di Động'),
('8f3b71b6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 3, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3b7247-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'parent', 'Parent', 'Parents', 'Mục Cha', 'Mục Cha', 'self', 0, 0, 0, 0, '', '', 2, 15, '', 'ben_desc', 'Parent', 'Mục Cha'),
('8f3b72d6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'ben_type', 'Benefit Type', 'Benefit Types', 'Loại Quyền Lợi', 'Loại Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Benefit Type', 'Loại Quyền Lợi'),
('8f3b735d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'ben_desc', 'Benefit Description', 'Benefit Descriptions', 'Mô Tả Quyền Lợi', 'Mô Tả Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Benefit Description', 'Mô Tả Quyền Lợi'),
('8f3b73e2-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'ben_desc_vi', 'Benefit Description (VN)', 'Benefit Descriptions (VN)', 'Mô Tả Quyền Lợi (VN)', 'Mô Tả Quyền Lợi (VN)', 'text', 1, 1, 0, 0, '', '', 5, 15, '', '', 'Benefit Description (VN)', 'Mô Tả Quyền Lợi (VN)'),
('8f3b746d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'ben_note', 'Benefit Note', 'Benefit Notes', 'Ghi Chú Quyền Lợi', 'Ghi Chú Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 6, 15, '', '', 'Benefit Note', 'Ghi Chú Quyền Lợi'),
('8f3b74f1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'note', 'Note', 'Notes', 'Ghi chú', 'Ghi chú', 'text', 1, 1, 0, 0, '', '', 12, 14, '', '', 'Note of the Mobile Claim', 'Ghi Chú của Bồi Thường Di Động'),
('8f3b7589-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'ben_note_vi', 'Benefit Note (VN)', 'Benefit Notes (VN)', 'Ghi Chú Quyền Lợi (VN)', 'Ghi Chú Quyền Lợi (VN)', 'text', 1, 1, 0, 0, '', '', 7, 15, '', '', 'Benefit Note (VN)', 'Ghi Chú Quyền Lợi (VN)'),
('8f3b760d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'gender', 'Gender', 'Genders', 'Giới Tính', 'Giới Tính', 'enum', 1, 0, 0, 0, '', '', 8, 15, '', '', 'Gender', 'Giới Tính'),
('8f3b769c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'pcv_head', 'Represent Benefit Head', 'Represent Benefit Heads', 'Đầu Quyền Lợi Đại Diện', 'Đầu Quyền Lợi Đại Diện', 'belong', 1, 1, 0, 0, '', '', 9, 15, 'code', 'code', 'Represent Benefit Head', 'Đầu Quyền Lợi Đại Diện'),
('8f3b7726-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'is_combined', 'Is Combined', 'Is Combined', 'Là Kết Hợp', 'Là Kết Hợp', 'checkbox', 0, 0, 0, 3, '', '', 10, 15, 'ben_desc', 'ben_desc', 'Is Combined', 'Là Kết Hợp'),
('8f3b77af-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_benefit', 'is_gop', 'Is Gop', 'Is Gop', 'Là Gop', 'Là Gop', 'checkbox', 0, 0, 0, 3, '', '', 11, 15, 'ben_desc', 'ben_desc', 'Is Gop', 'Là Gop'),
('8f3b7833-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của Thành viên'),
('8f3b78cc-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'mbr_name', 'Member Name', 'Member Names', 'Tên Thành viên', 'Tên Thành viên', 'text', 1, 1, 0, 255, '', '', 2, 3, '', '', 'Name of the Member', 'Tên của Thành viên'),
('8f3b7951-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'dob', 'Birth Date', 'Birth Dates', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 1, 0, 0, '', '', 3, 2, '', '', 'Birth Date of the Member', 'Ngày Sinh của Thành viên'),
('8f3b79dc-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'gender', 'Gender', 'Genders', 'Giới Tính', 'Giới Tính', 'enum', 1, 1, 0, 0, '', '', 4, 2, '', '', 'Gender of the Member', 'Giới Tính của Thành viên'),
('8f3b7a69-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'pocy_no', 'Policy No', 'Policy Nos', 'Số Hợp đồng', 'Số Hợp đồng', 'text', 1, 1, 0, 20, '', '', 5, 3, '', '', 'Policy No of the Member', 'Số Hợp đồng của Thành viên'),
('8f3b7af6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'dependent_memb_no', 'Dependent Member No', 'Dependent Member No', 'Mã thành viên người phụ thuộc', 'Mã thành viên người phụ thuộc', 'text', 0, 0, 0, 0, '', '', 13, 14, '', '', 'Dependent Member No of the Mobile Claim', 'Mã thành viên người phụ thuộc của Bồi Thường Di Động'),
('8f3b9b78-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'pocy_ref_no', 'Policy Ref No', 'Policy Ref Nos', 'Số Tham Chiếu Hợp đồng', 'Số Tham Chiếu Hợp đồng', 'text', 1, 1, 0, 30, '', '', 6, 2, '', '', 'Policy No of the Member', 'Số Tham Chiếu Hợp đồng của Thành viên'),
('8f3b9cb6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'mbr_no', 'Member No', 'Member Nos', 'Số Thành viên', 'Số Thành viên', 'text', 1, 1, 0, 20, '', '', 7, 3, '', '', 'Member No of the Member', 'Số Thành viên của Thành viên'),
('8f3b9d68-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'payment_mode', 'Payment Mode', 'Payment Modes', 'Phương Thức Thanh Toán', 'Phương Thức Thanh Toán', 'text', 1, 1, 0, 20, '', '', 8, 3, '', '', 'Payment Mode of the Member', 'Phương Thức Thanh Toán của Thành viên'),
('8f3b9e07-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'memb_eff_date', 'Effective Date', 'Effective Dates', 'Ngày Hiệu Lực', 'Ngày Hiệu Lực', 'date', 1, 1, 0, 0, '', '', 9, 3, '', '', 'Effective Date of the Member', 'Ngày Hiệu Lực của Thành viên'),
('8f3b9ea8-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'memb_exp_date', 'Expiry Date', 'Expiry Dates', 'Ngày Hết Hạn', 'Ngày Hết Hạn', 'date', 1, 1, 0, 0, '', '', 10, 3, '', '', 'Expiry Date of the Member', 'Ngày Hết Hạn của Thành viên'),
('8f3b9f42-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'term_date', 'Termination Date', 'Termination Dates', 'Ngày Chấm Dứt', 'Ngày Chấm Dứt', 'date', 1, 1, 0, 0, '', '', 11, 3, '', '', 'Termination Date of the Member', 'Ngày Chấm Dứt của Thành viên'),
('8f3b9fc8-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'reinst_date', 'Reinstate Date', 'Reinstate Dates', 'Ngày Tái tục', 'Ngày Ngày Tái tục', 'date', 1, 1, 0, 0, '', '', 12, 2, '', '', 'Reinstate Date of the Member', 'Ngày Ngày Tái tục của Thành viên'),
('8f3ba051-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'min_memb_eff_date', 'First Member Effective Date', 'First Member Effective Dates', 'Ngày Hiệu Lực Thành Viên Đầu Tiên', 'Ngày Hiệu Lực Thành Viên Đầu Tiên', 'date', 1, 1, 0, 0, '', '', 13, 2, '', '', 'First Member Effective Date of the Member', 'Ngày Hiệu Lực Thành Viên Đầu Tiên của Thành viên'),
('8f3ba0dd-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'min_pocy_eff_date', 'First Policy Effective Date', 'First Policy Effective Dates', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên', 'date', 1, 1, 0, 0, '', '', 14, 2, '', '', 'First Policy Effective Date of the Member', 'Ngày Hiệu Lực Hợp đồng Đầu Tiên của Thành viên'),
('8f3ba161-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'insured_periods', 'Insurance Periods', 'Insurance Periods', 'Kỳ Bảo hiểm', 'Kỳ Bảo hiểm', 'text', 1, 1, 0, 500, '', '', 15, 2, '', '', 'Insurance Periods of the Member', 'Những Kỳ Bảo hiểm của Thành viên'),
('8f3ba1e7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim', 'fullname', 'Full name', 'Full name', 'Họ tên đầy đủ', 'Họ tên đầy đủ', 'text', 0, 0, 0, 0, '', '', 14, 14, '', '', 'Full name of the Mobile Claim', 'Họ tên đầy đủ của Bồi Thường Di Động'),
('8f3ba278-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'wait_period', 'Wait Period', 'Wait Periods', 'Thời Kỳ Chờ', 'Thời Kỳ Chờ', 'enum', 1, 1, 0, 0, '', '', 16, 2, '', '', 'Wait Period of the Member', 'Thành viên có Thời Kỳ Chờ không?'),
('8f3ba303-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'spec_dis_period', 'Special Disease Wait Period', 'Special Disease Wait Periods', 'Thời Kỳ Chờ Bệnh Đặc Biệt', 'Thời Kỳ Chờ Bệnh Đặc Biệt', 'enum', 1, 1, 0, 0, '', '', 17, 2, '', '', 'Special Disease Wait Period of the Member', 'Thành viên có Thời Kỳ Chờ Bệnh Đặc Biệt không?'),
('8f3ba394-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'product', 'Product', 'Products', 'Sản Phẩm', 'Sản Phẩm', 'text', 1, 1, 0, 10, '', '', 18, 2, '', '', 'Product of the Member', 'Loại Sản Phẩm của Thành viên'),
('8f3ba41d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'plan_desc', 'Plan Description', 'Plan Descriptions', 'Gói Bảo Hiểm', 'Gói Bảo Hiểm', 'text', 1, 1, 0, 255, '', '', 19, 2, '', '', 'Plan Description of the Member', 'Gói Bảo Hiểm của Thành viên'),
('8f3ba4a2-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'memb_rstr', 'Restriction', 'Restrictions', 'Loại Trừ', 'Loại Trừ', 'textarea', 1, 1, 0, 9999, '', '', 20, 2, '', '', 'Restrictions of the Member', 'Các Loại Trừ của Thành viên'),
('8f3ba52b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'memb_rstr_vi', 'Restriction (VN)', 'Restrictions (VN)', 'Loại Trừ (VN)', 'Loại Trừ (VN)', 'textarea', 1, 1, 0, 9999, '', '', 21, 2, '', '', 'Restrictions (VN) of the Member', 'Các Loại Trừ (VN) của Thành viên'),
('8f3ba601-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'primary_broker_name', 'Broker', 'Brokers', 'Người Môi Giới', 'Người Môi Giới', 'text', 1, 1, 0, 255, '', '', 22, 2, '', '', 'Broker of the Member', 'Người Môi Giới của cho Thành viên'),
('8f3ba685-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'broker_name', 'Frontliner', 'Frontliners', 'Người Bán', 'Người Bán', 'text', 1, 1, 0, 255, '', '', 23, 2, '', '', 'Frontliner of the Member', 'Người Bán cho Thành viên'),
('8f3ba707-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'policy_status', 'Member Status', 'Member Statuses', 'Trạng Thái', 'Trạng Thái', 'text', 1, 1, 0, 255, '', '', 24, 3, '', '', 'Member Status of the Member', 'Trạng Thái của Thành viên'),
('8f3ba78d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'is_renew', 'Renew', 'Renew', 'Hợp Đồng Cũ', 'Hợp Đồng Cũ', 'enum', 1, 1, 0, 0, '', '', 25, 3, '', '', 'Renew of the Member', 'Là Thành viên Cũ phải không?'),
('8f3ba80f-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'ip_limit', 'IP Limit', 'IP Limits', 'Giới Hạn Nội Trú', 'Giới Hạn Nội Trú', 'double', 1, 0, 0, 0, '', '', 26, 2, '', '', 'IP Limit of the current item', 'Giới Hạn Nội Trú của Thành viên'),
('8f3ba894-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'op_limit_per_year', 'OP Limit/Year', 'OP Limits/Year', 'Giới Hạn Ngoại Trú/Năm', 'Giới Hạn Ngoại Trú/Năm', 'double', 1, 0, 0, 0, '', '', 27, 2, '', '', 'OP Limit/Year of the current item', 'Giới Hạn Ngoại Trú/Năm của Thành viên'),
('8f3ba91e-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'am_limit_per_year', 'Alt Medicines Limit/Year', 'Alt Medicines Limits/Year', 'Giới Hạn Đông Y/Năm', 'Giới Hạn Đông Y/Năm', 'double', 1, 0, 0, 0, '', '', 28, 2, '', '', 'Alt Medicines Limit/Year of the current item', 'Giới Hạn Đông Y/Năm của Thành viên'),
('8f3ba9a3-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'os_limit_per_year', 'OP Surgery Limit/Year', 'OP Surgery Limits/Year', 'Giới Hạn Phẫu Thuật Ngoại Trú/Năm', 'Giới Hạn Phẫu Thuật Ngoại Trú/Năm', 'double', 1, 0, 0, 0, '', '', 29, 2, '', '', 'OP Surgery Limit/Year of the current item', 'Giới Hạn Phẫu Thuật Ngoại Trú/Năm của Thành viên'),
('8f3baa26-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'op_copay_pct', 'OP Co-Payment Percentage', 'OP Co-Payment Percentage', 'Phần trăm Đồng Chi Trả cho Ngoại Trú', 'Phần trăm Đồng Chi Trả cho Ngoại Trú', 'integer', 1, 0, 0, 0, '', '', 30, 2, '', '', 'OP Co-Payment Percentage of the current item', 'Phần trăm Đồng Chi Trả cho Ngoại Trú của Thành viên'),
('8f3baaa6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'op_limit_per_visit', 'OP Limit/Visit', 'OP Limits/Visit', 'Giới Hạn Ngoại Trú/Năm', 'Giới Hạn Ngoại Trú/Năm', 'double', 1, 0, 0, 0, '', '', 31, 2, '', '', 'OP Limit/Visit of the current item', 'Giới Hạn Ngoại Trú/Năm của Thành viên'),
('8f3bab2a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'dt_limit_per_year', 'DT Limit/Year', 'DT Limits/Year', 'Giới Hạn Nha Khoa/Năm', 'Giới Hạn Nha Khoa/Năm', 'double', 1, 0, 0, 0, '', '', 32, 2, '', '', 'DT Limit/Year of the current item', 'Giới Hạn Nha Khoa/Năm của Thành viên'),
('8f3babaa-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'op_ind', 'OP Indicator', 'OP Indicators', 'Có Mua Ngoại Trú', 'Có Mua Ngoại Trú', 'enum', 1, 0, 0, 0, '', '', 33, 2, '', '', 'OP Indicator of the current item', 'Thành Viên Có Mua Ngoại Trú không?'),
('8f3bac2c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_member', 'has_op_debit_note', 'Has OP Debit Note', 'Has OP Debit Notes', 'Đã trả tiền OP', 'Đã trả tiền OP', 'enum', 1, 1, 0, 0, '', '', 34, 2, '', '', 'Has OP Debit Note of the Member', 'Thành viên đã trả tiền OP chưa?'),
('8f3bacab-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'form', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3bad40-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'form', 'name', 'Name', 'Names', 'Tên', 'Tên', 'text', 1, 1, 0, 50, '', '', 2, 15, '', '', 'File Name of the Form', 'Tên của Đơn'),
('8f3badc9-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'form', 'name_vi', 'Name (VN)', 'Names (VN)', 'Tên (VN)', 'Tên (VN)', 'text', 1, 1, 0, 50, '', '', 3, 15, '', '', 'File Name (VN) of the Form', 'Tên (VN) của Đơn'),
('8f3bae59-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'form', 'path', 'Path', 'Paths', 'Đường dẫn', 'Đường dẫn', 'file', 1, 1, 0, 0, '', '', 4, 14, '', '', 'Path of the Form', 'Đường dẫn của đơn'),
('8f3baee4-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'form', 'path_vi', 'Path (VN)', 'Paths (VN)', 'Đường dẫn (VN)', 'Đường dẫn (VN)', 'file', 1, 1, 0, 0, '', '', 5, 14, '', '', 'Path (VN) of the Form', 'Đường dẫn (VN) của đơn'),
('8f3baf79-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3bb019-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'mantis_id', 'Mantis Id', 'Mantis Ids', 'Mã số Mantis', 'Mã số Mantis', 'integer', 1, 1, 0, 11, '', '', 2, 2, '', '', 'Mantis Id number of the current item', 'Mã định danh Mantis của mục này'),
('8f3bb147-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'email', 'Email', 'Emails', 'Thư Điện Tử', 'Thư Điện Tử', 'email', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Email', 'Thư Điện Tử'),
('8f3bb1ed-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'email', 'Email', 'Emails', 'Thư điện tử', 'Thư điện tử', 'email', 1, 1, 0, 200, '', '', 5, 15, '', '', 'Email Address of the User', 'Thư điện tử của người dùng'),
('8f3bb298-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'ip_address', 'IP Address', 'IP Addresses', 'Địa Chỉ IP', 'Địa Chỉ IP', 'text', 1, 0, 0, 200, '', '', 4, 15, '', '', 'IP Address', 'Địa Chỉ IP'),
('8f3bb345-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'time', 'Time', 'Times', 'Thời điểm', 'Thời điểm', 'datetime', 1, 0, 0, 0, '', '', 5, 15, '', '', 'Time', 'Thời điểm'),
('8f3bb3ed-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'pocy_no', 'Policy No', 'Policy Nos', 'Mã Hợp Đồng', 'Mã Hợp Đồng', 'text', 1, 0, 0, 200, '', '', 6, 15, '', '', 'Policy No', 'Mã Hợp Đồng'),
('8f3bb49a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'mbr_no', 'Member No', 'Member Nos', 'Mã Thành Viên', 'Mã Thành Viên', 'text', 1, 0, 0, 200, '', '', 7, 15, '', '', 'Member No', 'Mã Thành Viên'),
('8f3bb545-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'dob', 'Date of Birth', 'Date of Births', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 0, 0, 200, '', '', 8, 15, '', '', 'Date of Birth', 'Ngày Sinh'),
('8f3bb5ea-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'provider', 'Provider', 'Providers', 'Cơ Sở Điều Trị', 'Cơ Sở Điều Trị', 'belong', 1, 0, 0, 0, '', '', 9, 15, 'name', 'name', 'Provider', 'Cơ Sở Điều Trị'),
('8f3bb689-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'incur_date', 'Incur Date From', 'Incur Date Froms', 'Ngày Khám', 'Ngày Khám', 'date', 1, 0, 0, 200, '', '', 10, 15, '', '', 'Incur Date From', 'Ngày Khám'),
('8f3bda1c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'diagnosis', 'Diagnosis', 'Diagnosises', 'Chẩn Đoán', 'Chẩn Đoán', 'text', 1, 0, 0, 0, '', '', 11, 14, '', '', 'Diagnosis', 'Chẩn Đoán'),
('8f3bdbbe-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'result', 'Result', 'Results', 'Kết Quả', 'Kết Quả', 'json', 0, 0, 0, 9999, '', '', 12, 14, '', '', 'Result', 'Kết Quả'),
('8f3bdc83-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_history', 'note', 'Note', 'Notes', 'Ghi chú', 'Ghi chú', 'text', 1, 0, 0, 200, '', '', 13, 15, '', '', 'Note', 'Ghi chú'),
('8f3bdd37-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_db_claim', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3bddf0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_db_claim', 'db_ref_no', 'DB Ref No', 'DB Ref Nos', 'Mã Tham Chiếu Bảo Lãnh', 'Mã Tham Chiếu Bảo Lãnh', 'text', 0, 0, 0, 200, '', '', 2, 15, '', '', 'DB Ref No', 'Mã Tham Chiếu Bảo Lãnh'),
('8f3bdea0-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_db_claim', 'pcv_history', 'PCV Direct Billing', 'PCV Direct Billings', ' Yêu cầu Thanh Toán của PCV', 'Yêu cầu Thanh Toán của PCV', 'belong', 1, 0, 0, 0, '', '', 3, 15, 'id', 'id', 'PCV Direct Billing', 'Yêu cầu Thanh Toán của PCV'),
('8f3bdf48-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_db_claim', 'pcv_head', 'Benefit Head', 'Benefit Heads', 'Đầu Quyền Lợi', 'Đầu Quyền Lợi', 'belong', 1, 0, 0, 0, '', '', 4, 15, 'code', 'code', 'Benefit Head', 'Đầu Quyền Lợi'),
('8f3bdfec-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_db_claim', 'pres_amt', 'Presented Amount', 'Presented Amounts', 'Số Tiền Yêu Cầu', 'Số Tiền Yêu Cầu', 'integer', 1, 0, 0, 0, '', '', 5, 15, '', '', 'Presented Amount', 'Số Tiền Yêu Cầu'),
('8f3be097-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_db_claim', 'app_amt', 'Approved Amount', 'Approved Amounts', 'Số Tiền Chấp Nhận', 'Số Tiền Chấp Nhận', 'integer', 1, 0, 0, 0, '', '', 6, 15, '', '', 'Approved Amount', 'Số Tiền Chấp Nhận'),
('8f3be134-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_db_claim', 'status', 'Status', 'Statuses', 'Trạng thái', 'Trạng thái', 'enum', 1, 0, 0, 200, '', '', 7, 15, 'id', '[\"Confirmed\",\"Canceled\",\"Deleted\"]', 'Status of the record', 'Trạng thái của bản ghi'),
('8f3be1d3-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_db_claim', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3be28a-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_db_claim', 'db_ref_no', 'DB Ref No', 'DB Ref Nos', 'Mã Tham Chiếu Bảo Lãnh', 'Mã Tham Chiếu Bảo Lãnh', 'text', 0, 0, 0, 200, '', '', 2, 15, '', '', 'DB Ref No', 'Mã Tham Chiếu Bảo Lãnh'),
('8f3be32d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_db_claim', 'fubon_history', 'Fubon Direct Billing', 'Fubon Direct Billings', ' Yêu cầu Thanh Toán của Fubon', 'Yêu cầu Thanh Toán của Fubon', 'belong', 1, 0, 0, 0, '', '', 3, 15, 'id', 'id', 'Fubon Direct Billing', 'Yêu cầu Thanh Toán của Fubon'),
('8f3be3d2-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_db_claim', 'fubon_head', 'Benefit Head', 'Benefit Heads', 'Đầu Quyền Lợi', 'Đầu Quyền Lợi', 'belong', 1, 0, 0, 0, '', '', 4, 15, 'code', 'code', 'Benefit Head', 'Đầu Quyền Lợi'),
('8f3be473-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_db_claim', 'pres_amt', 'Presented Amount', 'Presented Amounts', 'Số Tiền Yêu Cầu', 'Số Tiền Yêu Cầu', 'integer', 1, 0, 0, 0, '', '', 5, 15, '', '', 'Presented Amount', 'Số Tiền Yêu Cầu'),
('8f3be518-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_db_claim', 'app_amt', 'Approved Amount', 'Approved Amounts', 'Số Tiền Chấp Nhận', 'Số Tiền Chấp Nhận', 'integer', 1, 0, 0, 0, '', '', 6, 15, '', '', 'Approved Amount', 'Số Tiền Chấp Nhận'),
('8f3be5ba-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'fubon_db_claim', 'status', 'Status', 'Statuses', 'Trạng thái', 'Trạng thái', 'enum', 1, 0, 0, 200, '', '', 7, 15, 'id', '[\"Confirmed\",\"Canceled\",\"Deleted\"]', 'Status of the record', 'Trạng thái của bản ghi'),
('8f3bf8c1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3bfa04-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'mantis_id', 'Mantis Id', 'Mantis Ids', 'Mã số Mantis', 'Mã số Mantis', 'integer', 1, 1, 0, 11, '', '', 2, 2, '', '', 'Mantis Id number of the current item', 'Mã định danh Mantis của mục này'),
('8f3bfab6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'email', 'Email', 'Emails', 'Thư Điện Tử', 'Thư Điện Tử', 'email', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Email', 'Thư Điện Tử'),
('8f3bfb60-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'ip_address', 'IP Address', 'IP Addresses', 'Địa Chỉ IP', 'Địa Chỉ IP', 'text', 1, 0, 0, 200, '', '', 4, 15, '', '', 'IP Address', 'Địa Chỉ IP'),
('8f3bfc13-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'time', 'Time', 'Times', 'Thời điểm', 'Thời điểm', 'datetime', 1, 0, 0, 0, '', '', 5, 15, '', '', 'Time', 'Thời điểm'),
('8f3bfcbf-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'pocy_no', 'Policy No', 'Policy Nos', 'Mã Hợp Đồng', 'Mã Hợp Đồng', 'text', 1, 0, 0, 200, '', '', 6, 15, '', '', 'Policy No', 'Mã Hợp Đồng'),
('8f3bfd61-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'mbr_no', 'Member No', 'Member Nos', 'Mã Thành Viên', 'Mã Thành Viên', 'text', 1, 0, 0, 200, '', '', 7, 15, '', '', 'Member No', 'Mã Thành Viên'),
('8f3bfe00-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'dob', 'Date of Birth', 'Date of Births', 'Ngày Sinh', 'Ngày Sinh', 'date', 1, 0, 0, 200, '', '', 8, 15, '', '', 'Date of Birth', 'Ngày Sinh'),
('8f3bfe99-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'provider', 'Provider', 'Providers', 'Cơ Sở Điều Trị', 'Cơ Sở Điều Trị', 'belong', 1, 0, 0, 0, '', '', 9, 15, 'name', 'name', 'Provider', 'Cơ Sở Điều Trị'),
('8f3bff37-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'incur_date', 'Incur Date From', 'Incur Date Froms', 'Ngày Khám', 'Ngày Khám', 'date', 1, 0, 0, 200, '', '', 10, 15, '', '', 'Incur Date From', 'Ngày Khám'),
('8f3bffd6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'diagnosis', 'Diagnosis', 'Diagnosises', 'Chẩn Đoán', 'Chẩn Đoán', 'text', 1, 0, 0, 0, '', '', 11, 14, '', '', 'Diagnosis', 'Chẩn Đoán'),
('8f3c006d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'result', 'Result', 'Results', 'Kết Quả', 'Kết Quả', 'json', 0, 0, 0, 9999, '', '', 12, 14, '', '', 'Result', 'Kết Quả'),
('8f3c0101-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_history', 'note', 'Note', 'Notes', 'Ghi chú', 'Ghi chú', 'text', 1, 0, 0, 200, '', '', 13, 15, '', '', 'Note', 'Ghi chú'),
('8f3c01a6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user_bank_account', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3c0257-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_db_claim', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3c0312-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_db_claim', 'db_ref_no', 'DB Ref No', 'DB Ref Nos', 'Mã Tham Chiếu Bảo Lãnh', 'Mã Tham Chiếu Bảo Lãnh', 'text', 0, 0, 0, 200, '', '', 2, 15, '', '', 'DB Ref No', 'Mã Tham Chiếu Bảo Lãnh'),
('8f3c03b4-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_db_claim', 'cathay_history', 'Cathay Direct Billing', 'Cathay Direct Billings', ' Yêu cầu Thanh Toán của Cathay', 'Yêu cầu Thanh Toán của Cathay', 'belong', 1, 0, 0, 0, '', '', 3, 15, 'id', 'id', 'Cathay Direct Billing', 'Yêu cầu Thanh Toán của Cathay'),
('8f3c0460-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_db_claim', 'cathay_head', 'Benefit Head', 'Benefit Heads', 'Đầu Quyền Lợi', 'Đầu Quyền Lợi', 'belong', 1, 0, 0, 0, '', '', 4, 15, 'code', 'code', 'Benefit Head', 'Đầu Quyền Lợi'),
('8f3c0501-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_db_claim', 'pres_amt', 'Presented Amount', 'Presented Amounts', 'Số Tiền Yêu Cầu', 'Số Tiền Yêu Cầu', 'integer', 1, 0, 0, 0, '', '', 5, 15, '', '', 'Presented Amount', 'Số Tiền Yêu Cầu'),
('8f3c05a6-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_db_claim', 'app_amt', 'Approved Amount', 'Approved Amounts', 'Số Tiền Chấp Nhận', 'Số Tiền Chấp Nhận', 'integer', 1, 0, 0, 0, '', '', 6, 15, '', '', 'Approved Amount', 'Số Tiền Chấp Nhận'),
('8f3c063b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'cathay_db_claim', 'status', 'Status', 'Statuses', 'Trạng thái', 'Trạng thái', 'enum', 1, 0, 0, 200, '', '', 7, 15, 'id', '[\"Confirmed\",\"Canceled\",\"Deleted\"]', 'Status of the record', 'Trạng thái của bản ghi'),
('8f3c06d1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user_bank_account', 'mobile_user', 'Mobile User', 'Mobile Users', 'Người dùng Di Động', 'Người dùng Di Động', 'belong', 1, 1, 0, 50, '', '', 2, 15, 'mbr_no', 'mbr_no', 'Mobile User of the Bank Account', 'Người dùng di động của Tài Khoản'),
('8f3c0775-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user_bank_account', 'bank_name', 'Bank Name', 'Bank Names', 'Tên Ngân Hàng', 'Tên Ngân Hàng', 'text', 1, 1, 0, 50, '', '', 3, 15, '', '', 'Bank Name of the Account', 'Tên Ngân Hàng của Tài Khoản'),
('8f3c0811-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user_bank_account', 'bank_address', 'Bank Address', 'Bank Addresses', 'Địa Chỉ Ngân Hàng', 'Tên Ngân Hàng', 'text', 1, 1, 0, 50, '', '', 4, 15, '', '', 'Bank Address of the Account', 'Địa Chỉ Ngân Hàng của Tài Khoản'),
('8f3c08b7-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user_bank_account', 'bank_acc_no', 'Bank Account No', 'Bank Account Nos', 'Mã Tài Khoản Ngân Hàng', 'Mã Tài Khoản Ngân Hàng', 'text', 1, 0, 0, 50, '', '', 5, 12, '', '', 'Bank Account No of the Account', 'Mã Tài Khoản Ngân Hàng của Tài Khoản'),
('8f3c095b-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'lzarole', 'Role', 'Roles', 'Vai trò', 'Vai trò', 'belong', 1, 0, 0, 0, '', '', 6, 15, 'name', 'name', 'Specify is this user can access Admin Panel or not', 'Xác định Người dùng thày có vào Bảng Quản Trị được không'),
('8f3c09fd-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_user_bank_account', 'bank_acc_name', 'Bank Account Name', 'Bank Account Names', 'Tên Tài Khoản Ngân Hàng', 'Tên Tài Khoản Ngân Hàng', 'text', 1, 0, 0, 50, '', '', 6, 15, '', '', 'Bank Account Name of the Account', 'Tên Tài Khoản Ngân Hàng của Tài Khoản'),
('8f3c0a9d-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim_status', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Mantis Id number of the current item', 'Mã Mantis của mục này'),
('8f3c0b43-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim_status', 'code', 'Code', 'Codes', 'Mã', 'Mã', 'integer', 1, 1, 0, 3, '', '', 2, 15, '', '', 'Code of the Mobile Claim Status', 'Mã của Trạng Thái của Bồi Thường Di Động'),
('8f3c0be1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim_status', 'name', 'Name', 'Names', 'Tên', 'Tên', 'text', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Name of the Mobile Claim Status', 'Tên của Trạng Thái của Bồi Thường Di Động'),
('8f3c0c7c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_claim_status', 'name_vi', 'Name (VN)', 'Names (VN)', 'Tên (VN)', 'Tên (VN)', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Name of the Mobile Claim Status', 'Tên của Trạng Thái của Bồi Thường Di Động'),
('8f3c0d18-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_device', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 0, 0, 11, '', '', 1, 2, '', '', 'Mantis Id number of the current item', 'Mã Mantis của mục này'),
('8f3c0dc1-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_device', 'mobile_user', 'Mobile User', 'Mobile Users', 'Người dùng Di Động', 'Người dùng Di Động', 'belong', 1, 1, 0, 50, '', '', 3, 15, 'mbr_no', 'mbr_no', 'Mobile User of the Mobile Device', 'Người dùng di động của Thiết Bị Di Động'),
('8f3c0e61-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'mobile_device', 'device_token', 'Device Token', 'Device Tokens', 'Mã Thiết bị', 'Mã Thiết bị', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Token of the Mobile Device', 'Mã của Thiết Bị Di Động'),
('8f3c0f03-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_head', 'id', 'Id', 'Ids', 'Mã định danh', 'Mã định danh', 'text', 1, 1, 0, 11, '', '', 1, 2, '', '', 'Id number of the current item', 'Mã định danh của mục này'),
('8f3c0faa-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_head', 'code', 'Code', 'Codes', 'Mã', 'Mã', 'text', 1, 1, 0, 0, '', '', 2, 15, '', '', 'Code', 'Mã'),
('8f3c1057-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'provider', 'Provider', 'Providers', 'Nhà cung cấp', 'Nhà cung cấp', 'belong', 1, 0, 0, 0, '', '', 7, 15, 'name', 'name', 'Provider of the User', 'Nhà cung cấp của người dùng'),
('8f3c1103-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_head', 'ben_heads', 'Ben Heads', 'Ben Heads', 'Quyền Lợi', 'Quyền Lợi', 'text', 1, 1, 0, 0, '', '', 3, 15, '', '', 'Ben Heads', 'Quyền Lợi'),
('8f3c119c-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_head', 'name', 'Name', 'Names', 'Tên', 'Tên', 'text', 1, 1, 0, 0, '', '', 4, 15, '', '', 'Name', 'Tên'),
('8f3c1235-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_head', 'name_vi', 'Name (VN)', 'Names (VN)', 'Tên (VN)', 'Tên (VN)', 'text', 1, 1, 0, 0, '', '', 5, 15, '', '', 'Name (VN)', 'Tên (VN)'),
('8f3c12d4-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'pcv_head', 'pcv_benefit', 'Primary Benefit', 'Primary Benefits', 'Quyền Lợi Chính', 'Quyền Lợi Chính', 'belong', 1, 0, 0, 0, '', '', 6, 15, 'ben_desc', 'ben_desc', 'Primary Benefit', 'Quyền Lợi Chính'),
('8f3c1366-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'notify', 'Notify', 'Notifies', 'Thông báo', 'Thông báo', 'checkbox', 0, 0, 0, 1, '', '', 8, 14, '', '', 'Specify will this user''s received notification emails of Order Request and Contact Information or not', 'Xác định người dùng này có nhận được email yêu cầu đặt hàng và Thông tin Tham Chiếu hay không'),
('8f3c1408-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'user', 'enabled', 'Enabled', 'Enabled', 'Kích hoạt', 'Kích hoạt', 'checkbox', 0, 0, 0, 1, '', '', 9, 14, '', '[\"Yes\",\"No\"]', 'Specify will this user is enabled or not', 'Xác định người dùng này có được kích hoạt hay không');

--
-- Triggers `lzafield`
--
DROP TRIGGER IF EXISTS `lzafield__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzafield__id` BEFORE INSERT ON `lzafield` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzafilter__id` BEFORE INSERT ON `lzafilter` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzahttprequest__id` BEFORE INSERT ON `lzahttprequest` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzalanguage`
--

DROP TABLE IF EXISTS `lzalanguage`;
CREATE TABLE `lzalanguage` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL,
  `order_by` int(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzalanguage`
--

TRUNCATE TABLE `lzalanguage`;
--
-- Dumping data for table `lzalanguage`
--

INSERT INTO `lzalanguage` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `code`, `order_by`) VALUES
('a3ef3d28-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'English', '', 1),
('a3ef6aec-4a64-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Tiếng Việt', '_vi', 2);

--
-- Triggers `lzalanguage`
--
DROP TRIGGER IF EXISTS `lzalanguage__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzalanguage__id` BEFORE INSERT ON `lzalanguage` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzamodule`
--

DROP TABLE IF EXISTS `lzamodule`;
CREATE TABLE `lzamodule` (
  `id` char(50) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `db_id` varchar(20) NOT NULL DEFAULT 'main',
  `parent` varchar(50) DEFAULT NULL,
  `icon` varchar(50) NOT NULL,
  `single` varchar(50) NOT NULL,
  `plural` varchar(50) NOT NULL,
  `single_vi` varchar(50) NOT NULL,
  `plural_vi` varchar(50) NOT NULL,
  `note` varchar(500) NOT NULL,
  `note_vi` varchar(500) NOT NULL,
  `display` varchar(100) NOT NULL,
  `unique_keys` varchar(255) NOT NULL,
  `sort` varchar(100) NOT NULL,
  `enabled` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `settings` varchar(500) NOT NULL,
  `order_by` int(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzamodule`
--

TRUNCATE TABLE `lzamodule`;
--
-- Dumping data for table `lzamodule`
--

INSERT INTO `lzamodule` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `db_id`, `parent`, `icon`, `single`, `plural`, `single_vi`, `plural_vi`, `note`, `note_vi`, `display`, `unique_keys`, `sort`, `enabled`, `settings`, `order_by`) VALUES
('cathay', 'admim', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'Cathay', 'Cathay', 'Cathay', 'Cathay', '', '', '', '', '', 'Yes', '', 7),
('cathay_benefit', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'file', 'Cathay Benefit', 'Cathay Benefits', 'Quyền Lợi Cathay', 'Quyền Lợi Cathay', 'Store Cathay Benefits', 'Chứa Quyền lợi Cathay', 'ben_desc', 'ben_desc', '[\"1\",\"asc\"]', 'Yes', '', 1),
('cathay_claim_line', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'user', 'Cathay Claim Line', 'Cathay Claim Lines', 'Dòng Bồi thường Cathay', 'Dòng Bồi thường Cathay', 'Store Cathay Claim Line Information', 'Chứa thông tin Dòng bồi thường của Cathay', 'cl_no', 'cl_no', '[1,\"asc\"]', 'Yes', '', 4),
('cathay_db_claim', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'history', 'Cathay Direct Billing Claim', 'Cathay Direct Billing Claims', 'Thanh Toán Trực Tiếp Cathay', 'Bồi Thường Thanh Toán Trực Tiếp của Cathay', 'Store Cathay Direct Billing Claims', 'Chứa Bồi Thường Thanh Toán Trực Tiếp của Cathay', 'db_ref_no', 'db_ref_no', '[\"1\",\"asc\"]', 'Yes', '', 6),
('cathay_head', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'file', 'Cathay Benefit Head', 'Cathay Benefit Heads', 'Loại Quyền Lợi Cathay', 'Loại Quyền Lợi Cathay', 'Store Cathay Benefit Heads', 'Chứa Đầu Quyền lợi Cathay', 'code', 'code', '[\"1\",\"asc\"]', 'Yes', '', 2),
('cathay_history', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'history', 'Cathay Check Card/Direct Billing History', 'Cathay Check Card/Direct Billing Histories', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Cathay', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Cathay', 'Store Cathay Check Card/Direct Billing Histories', 'Chứa Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Cathay', 'mbr_no', 'mbr_no', '[\"1\",\"asc\"]', 'Yes', '{\"history\": true}', 5),
('cathay_member', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'cathay', 'user', 'Cathay Member', 'Cathay Members', 'Thành viên Cathay', 'Thành viên Cathay', 'Store Cathay Member Information', 'Chứa thông tin Thành Viên Cathay', 'mbr_name', 'mbr_name', '[1,\"asc\"]', 'Yes', '', 3),
('form', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'file', 'Form', 'Forms', 'Đơn', 'Đơn', 'Store Form', 'Chứa đơn', 'filename', 'filename', '[\"1\",\"asc\"]', 'Yes', '', 4),
('fubon', 'admim', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'Fubon', 'Fubon', 'Fubon', 'Fubon', '', '', '', '', '', 'Yes', '', 6),
('fubon_benefit', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'file', 'Fubon Benefit', 'Fubon Benefits', 'Quyền Lợi Fubon', 'Quyền Lợi Fubon', 'Store Fubon Benefits', 'Chứa Quyền lợi Fubon', 'ben_desc', 'ben_desc', '[\"1\",\"asc\"]', 'Yes', '', 1),
('fubon_claim_line', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'user', 'Fubon Claim Line', 'Fubon Claim Lines', 'Dòng Bồi thường Fubon', 'Dòng Bồi thường Fubon', 'Store Fubon Claim Line Information', 'Chứa thông tin Dòng bồi thường của Fubon', 'cl_no', 'cl_no', '[1,\"asc\"]', 'Yes', '', 4),
('fubon_client', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'user', 'Fubon Client', 'Fubon Clients', 'Khách Hàng Fubon', 'Khách Hàng Fubon', 'Store Fubon Client Information', 'Chứa Thông tin Khách Hàng Fubon', 'mbr_name', 'poho_no,mbr_name,dob,gender', '[1,\"asc\"]', 'Yes', '', 5),
('fubon_db_claim', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'history', 'Fubon Direct Billing Claim', 'Fubon Direct Billing Claims', 'Thanh Toán Trực Tiếp Fubon', 'Bồi Thường Thanh Toán Trực Tiếp của Fubon', 'Store Fubon Direct Billing Claims', 'Chứa Bồi Thường Thanh Toán Trực Tiếp của Fubon', 'db_ref_no', 'db_ref_no', '[\"1\",\"asc\"]', 'Yes', '', 7),
('fubon_head', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'file', 'Fubon Benefit Head', 'Fubon Benefit Heads', 'Loại Quyền Lợi Fubon', 'Loại Quyền Lợi Fubon', 'Store Fubon Benefit Heads', 'Chứa Đầu Quyền lợi Fubon', 'code', 'code', '[\"1\",\"asc\"]', 'Yes', '', 2),
('fubon_history', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'history', 'Fubon Check Card/Direct Billing History', 'Fubon Check Card/Direct Billing Histories', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Fubon', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Fubon', 'Store Fubon Check Card/Direct Billing Histories', 'Chứa Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của Fubon', 'mbr_no', 'mbr_no', '[\"1\",\"asc\"]', 'Yes', '{\"history\": true}', 6),
('fubon_member', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'fubon', 'user', 'Fubon Member', 'Fubon Members', 'Thành viên Fubon', 'Thành viên Fubon', 'Store Fubon Member Information', 'Chứa thông tin Thành Viên Fubon', 'mbr_name', 'mbr_name', '[1,\"asc\"]', 'Yes', '', 3),
('lzaapi', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'user', 'API Token', 'API Tokens', 'Mã API', 'Mã API', 'API Token', 'Mã API', 'username', '', '[1,\"asc\"]', 'Yes', '', 7),
('lzaemail', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'Email', 'Emails', 'Thư Điện Tử', 'Thư Điện Tử', 'Email', 'Thư Điện Tử', 'subject', '', '[1,\"asc\"]', 'Yes', '', 16),
('lzafield', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'edit', 'Field', 'Fields', 'Mục', 'Mục', 'Field', 'Mục', 'single', '', '[1,\"asc\"]', 'Yes', '', 10),
('lzafilter', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'filter', 'Filter', 'Filters', 'Bộ Lọc', 'Bộ Lọc', 'Filter', 'Bộ Lọc', 'name', '', '[1,\"asc\"]', 'Yes', '', 3),
('lzahttprequest', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'Http Request', 'Http Requests', 'Yêu Cầu Http', 'Yêu Cầu Http', 'Http Request', 'Yêu Cầu Http', 'url', '', '[1,\"asc\"]', 'Yes', '', 18),
('lzalanguage', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'language', 'Language', 'Languages', 'Ngôn Ngữ', 'Ngôn Ngữ', 'Language', 'Ngôn Ngữ', 'name', '', '[1,\"asc\"]', 'Yes', '', 11),
('lzamodule', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'table', 'Module', 'Modules', 'lzamodule', 'lzamodule', 'Module', 'lzamodule', 'id', '', '[1,\"asc\"]', 'Yes', '', 8),
('lzanotification', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'Notification', 'Notifications', 'Thông Báo', 'Thông Báo', 'Notification', 'Thông Báo', 'subject', '', '[1,\"asc\"]', 'Yes', '', 17),
('lzapermission', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'edit', 'Permission', 'Permissions', 'Quyền', 'Quyền', 'Permission', 'Quyền', 'id', '', '[1,\"asc\"]', 'Yes', '', 4),
('lzarole', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'users', 'Role', 'Roles', 'Vai Trò', 'Vai Trò', 'Role', 'Vai Trò', 'name', '', '[1,\"asc\"]', 'Yes', '', 1),
('lzasection', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'gears', 'Section', 'Sections', 'Phần', 'Phần', 'Section', 'Phần', 'name', '', '[1,\"asc\"]', 'Yes', '', 13),
('lzasession', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'refresh', 'Session', 'Sessions', 'Phiên', 'Phiên', 'Session', 'Phiên', 'start', '', '[1,\"asc\"]', 'Yes', '', 5),
('lzasetting', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'gears', 'Setting', 'Settings', 'Cấu Hình', 'Cấu Hình', 'Setting', 'Cấu Hình', 'key', '', '[1,\"asc\"]', 'Yes', '', 14),
('lzasms', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'envelope', 'SMS Message', 'SMS Messages', 'Tin Nhắn SMS', 'Tin Nhắn SMS', 'SMS Message', 'Tin Nhắn SMS', 'receiver', '', '[1,\"asc\"]', 'Yes', '', 19),
('lzastatistic', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'bar-chart-o', 'Statistic', 'Statistics', 'Thống Kê', 'Thống Kê', 'Statistic', 'Thống Kê', 'name', '', '[1,\"asc\"]', 'Yes', '', 6),
('lzatask', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'tasks', 'Task', 'Tasks', 'Tác Vụ', 'Tác Vụ', 'Task', 'Tác Vụ', 'name', '', '[1,\"asc\"]', 'Yes', '', 15),
('lzatext', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'file-text', 'Text', 'Texts', 'Văn Bản', 'Văn Bản', 'Text', 'Văn Bản', 'name', '', '[1,\"asc\"]', 'Yes', '', 12),
('lzauser', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'user', 'User', 'Users', 'Ngươi Dùng', 'Ngươi Dùng', 'User', 'Ngươi Dùng', 'username', '', '[1,\"asc\"]', 'Yes', '', 2),
('lzaview', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'system', 'table', 'DB View', 'DB Views', 'Góc Nhìn Dữ Liệu', 'Góc Nhìn Dữ Liệu', 'DB View', 'Góc Nhìn Dữ Liệu', 'name', '', '[1,\"asc\"]', 'Yes', '', 9),
('mobile', 'admim', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'Mobile', 'Mobiles', 'Di Động', 'Di Động', '', '', '', '', '', 'Yes', '', 8),
('mobile_claim', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Claim', 'Mobile Claims', 'Bồi thường Di Động', 'Bồi thường Di Động', 'Store Mobile Claim Information', 'Chứa Thông Tin Bồi Thường Di động', 'id', 'id', '[\"1\",\"asc\"]', 'Yes', '', 5),
('mobile_claim_file', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Claim File', 'Mobile Claim Files', 'Tập Tin Bồi Thường Di Động', 'Tập Tin Bồi Thường Di Động', 'Store Mobile Claim File Information', 'Chứa Thông Tin Tập Tin của Bồi Thường Di động', 'filename', 'filename', '[\"1\",\"asc\"]', 'Yes', '', 6),
('mobile_claim_status', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Claim Status', 'Mobile Claim Statuses', 'Trạng Thái của Bồi thường Di Động', 'Trạng Thái của Bồi thường Di Động', 'Store Mobile Claim Status Information', 'Chứa Thông Tin Trạng Thái Bồi Thường Di động', 'name', 'name', '[\"1\",\"asc\"]', 'Yes', '', 4),
('mobile_device', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'file', 'Mobile Device', 'Mobile Devices', 'Thiết Bị Di Động', 'Thiết Bị Di Động', 'Store Mobile Device Information', 'Chứa Thông Tin Thiết Bị Di động', 'name', 'name', '[\"1\",\"asc\"]', 'Yes', '', 2),
('mobile_user', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'user', 'Mobile User', 'Mobile Users', 'Người dùng Di Động', 'Người dùng Di Động', 'Store Mobile User Information to login', 'Chứa Thông tin Người dùng di động', 'fullname', 'fullname', '[\"1\",\"asc\"]', 'Yes', '', 1),
('mobile_user_bank_account', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'mobile', 'user', 'Mobile User Bank Account', 'Mobile User Bank Accounts', 'Tài khoản Ngân Hàng của Người dùng Di Động', 'Tài khoản Ngân Hàng của Người dùng Di Động', 'Store Mobile User Bank Accounts', 'Chứa Thông tin Tài Khoản của Người dùng di động', 'bank_acc_no', 'bank_acc_no', '[\"1\",\"asc\"]', 'Yes', '', 3),
('pcv', 'admim', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'user', 'PCV', 'PCV', 'PCV', 'PCV', '', '', '', '', '', 'Yes', '', 5),
('pcv_benefit', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'file', 'PCV Benefit', 'PCV Benefits', 'Quyền Lợi PCV', 'Quyền Lợi PCV', 'Store PCV Benefits', 'Chứa Quyền Lợi PCV', 'ben_desc', 'ben_desc', '[\"1\",\"asc\"]', 'Yes', '', 1),
('pcv_claim_line', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'user', 'PCV Claim Line', 'PCV Claim Lines', 'Dòng Bồi thường PCV', 'Dòng Bồi thường PCV', 'Store PCV Claim Line Information', 'Chứa thông tin Dòng bồi thường của PCV', 'cl_no', 'cl_no', '[1,\"asc\"]', 'Yes', '', 5),
('pcv_db_claim', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'history', 'PCV Direct Billing Claim', 'PCV Direct Billing Claims', 'Thanh Toán Trực Tiếp PCV', 'Bồi Thường Thanh Toán Trực Tiếp của PCV', 'Store PCV Direct Billing Claims', 'Chứa Bồi Thường Thanh Toán Trực Tiếp của PCV', 'db_ref_no', 'db_ref_no', '[\"1\",\"asc\"]', 'Yes', '', 7),
('pcv_head', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'file', 'PCV Benefit Head', 'PCV Benefit Heads', 'Loại Quyền Lợi PCV', 'Loại Quyền Lợi PCV', 'Store PCV Benefit Heads', 'Chứa Đầu Quyền', 'code', 'code', '[\"1\",\"asc\"]', 'Yes', '', 2),
('pcv_history', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'history', 'PCV Check Card/Direct Billing History', 'PCV Check Card/Direct Billing Histories', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của PCV', 'Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của PCV', 'Store PCV Check Card/Direct Billing Histories', 'Chứa Lịch Sử Kiểm Tra Thẻ/Yêu Cầu Thanh Toán của PCV', 'mbr_no', 'mbr_no', '[\"1\",\"asc\"]', 'Yes', '{\"history\": true}', 6),
('pcv_member', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'user', 'PCV Member', 'PCV Members', 'Thành viên PCV', 'Thành viên PCV', 'Store PCV Member Information', 'Chứa thông tin Thành Viên PCV', 'mbr_name', 'mbr_name', '[1,\"asc\"]', 'Yes', '', 4),
('pcv_plan_desc_map', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', 'pcv', 'file', 'PCV Plan Desc Map', 'PCV Plan Desc Maps', 'Ánh Xạ Gói Bảo Hiểm PCV', 'Ánh Xạ Gói Bảo Hiểm PCV', 'Store PCV Plan Desc Maps', 'Chứa Bản Đồ Gói BH của PCV', 'haystack', 'haystack,needle', '[\"1\",\"asc\"]', 'Yes', '', 3),
('post', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'code', 'Post', 'Posts', 'Bài viết', 'Bài viết', 'Store Metadata and also content of front end web pages', 'Chứa Siêu Dữ Liệu và Nội dung của trang web', 'slug', 'slug', '[\"1\",\"asc\"]', 'Yes', '', 2),
('provider', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'user-md', 'Provider', 'Providers', 'Nhà cung cấp', 'Nhà cung cấp', 'Store Provider Ìnormation', 'Chứa thông tin Nhà cung cấp', 'name', 'name', '[\"1\",\"asc\"]', 'Yes', '', 3),
('system', 'admim', '2019-12-31 17:00:00', NULL, NULL, '', NULL, 'lock', 'System', 'Systems', 'Hệ Thống', 'Hệ Thống', '', '', '', '', '', 'Yes', '', 0),
('user', 'admim', '2019-12-31 17:00:00', NULL, NULL, 'main', NULL, 'user', 'User', 'Users', 'Người dùng', 'Người dùng', 'Store User Information to login and access admin panel', 'Chứa thông tin Người Dùng để đăng nhập và truy cập bảng Quản Trị', 'fullname', 'email,username', '[\"1\",\"asc\"]', 'Yes', '', 1);

--
-- Triggers `lzamodule`
--
DROP TRIGGER IF EXISTS `lzamodule__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzamodule__id` AFTER INSERT ON `lzamodule` FOR EACH ROW BEGIN SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzanotification__id` BEFORE INSERT ON `lzanotification` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzapermission`
--

DROP TABLE IF EXISTS `lzapermission`;
CREATE TABLE `lzapermission` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzarole_id` char(36) NOT NULL,
  `lzamodule_id` char(50) NOT NULL,
  `level` int(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzapermission`
--

TRUNCATE TABLE `lzapermission`;
--
-- Triggers `lzapermission`
--
DROP TRIGGER IF EXISTS `lzapermission__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzapermission__id` BEFORE INSERT ON `lzapermission` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzarole`
--

DROP TABLE IF EXISTS `lzarole`;
CREATE TABLE `lzarole` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(50) NOT NULL,
  `name_vi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzarole`
--

TRUNCATE TABLE `lzarole`;
--
-- Dumping data for table `lzarole`
--

INSERT INTO `lzarole` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `name_vi`) VALUES
('2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'User', 'Người Dùng'),
('2e6887d6-4a66-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'New Business', 'Thẩm Định'),
('2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Customer Service', 'Dịch Vụ Khách Hàng');

--
-- Triggers `lzarole`
--
DROP TRIGGER IF EXISTS `lzarole__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzarole__id` BEFORE INSERT ON `lzarole` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzasection`
--

DROP TABLE IF EXISTS `lzasection`;
CREATE TABLE `lzasection` (
  `id` char(50) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `title` varchar(50) NOT NULL,
  `title_vi` varchar(50) NOT NULL,
  `order_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzasection`
--

TRUNCATE TABLE `lzasection`;
--
-- Dumping data for table `lzasection`
--

INSERT INTO `lzasection` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `title`, `title_vi`, `order_by`) VALUES
('setting_datetime', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Datetime', 'Ngày Giờ', 3),
('setting_information', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Information', 'Thông Tin', 4),
('setting_mobile_claim', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Mobile Claims', 'Bồi Thường Di Động', 5),
('setting_password', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Password', 'Mật khẩu', 2),
('setting_smtp', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Outgoing Email', 'Email ra', 1);

--
-- Triggers `lzasection`
--
DROP TRIGGER IF EXISTS `lzasection__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzasection__id` AFTER INSERT ON `lzasection` FOR EACH ROW BEGIN SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzasession__id` BEFORE INSERT ON `lzasession` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzasetting`
--

DROP TABLE IF EXISTS `lzasetting`;
CREATE TABLE `lzasetting` (
  `id` char(50) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzasection_id` char(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_vi` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `type` enum('integer','float','double','text','textarea','html','password','email','phone','link','enum','checkbox','file','date','datetime','eventstart','eventend') NOT NULL DEFAULT 'text',
  `extra` text NOT NULL,
  `order_by` int(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzasetting`
--

TRUNCATE TABLE `lzasetting`;
--
-- Dumping data for table `lzasetting`
--

INSERT INTO `lzasetting` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzasection_id`, `title`, `title_vi`, `value`, `type`, `extra`, `order_by`) VALUES
('adult_age', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_mobile_claim', 'Adult Age', 'Tuổi Thành Niên', '5', 'integer', '', 1),
('cathay', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'Cathay', 'Cathay', 'CATHAY LIFE VIETNAM', 'text', '', 3),
('company_name', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'Company Name', 'Tên Công Ty', 'Pacific Cross Vietnam', 'text', '', 1),
('datetime_format', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_datetime', 'Date Time Format', 'Định dạng Ngày Giờ', '%d/%m/%Y %H:%i:%s', 'enum', '[\"%d/%m/%Y %H:%i:%s\",\"%m/%d/%Y %H:%i:%s\",\"%Y/%m/%d %H:%i:%s\",\"%d-%m-%Y %H:%i:%s\",\"%m-%d-%Y %H:%i:%s\",\"%Y-%m-%d %H:%i:%s\"]', 2),
('date_format', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_datetime', 'Date Format', 'Định dạng Ngày', '%d/%m/%Y', 'enum', '[\"%d/%m/%Y\",\"%m/%d/%Y\",\"%Y/%m/%d\",\"%d-%m-%Y\",\"%m-%d-%Y\",\"%Y-%m-%d\"]', 1),
('email', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'Company Email', 'Thư điện tử Công Ty', 'inquiry@pacificcross.com.vn', 'text', '', 2),
('exclusion', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'Exclusion', 'Loại Trừ', '<strong>Direct Billing is not applied for: </strong><ul><li>Maternity;</li><li>Medical Check-up;</li><li>Screening tests;</li><li>Vitamin, mineral, supplement, functional foods, cosmeceuticals, medical appliances not used for surgery, unregistered drugs, any other non-medicinal products, etc.;</li><li>Waiting period;</li>\r\n<li> Physiotherapy: Guarantee up to 5 times. After 5 treatments, there should be reevaluation of the doctor according to the Progress Note form; </li>\r\n<li><a href=\"/resources/files/exclusion_en.pdf\">Exclusion list.</a></li></ul>', 'html', '', 6),
('exclusion_vi', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'Exclusion (VN)', 'Loại Trừ (VN)', '<strong>Không Áp Dụng Bảo Lãnh Viện Phí cho:</strong><ul><li>Thai sản;</li><li>Kiểm tra sức khỏe định kỳ;</li><li>Xét nghiệm tầm soát;</li><li>Vitamin, khoáng chất, thực phẩm chức năng, thuốc bổ, dược mỹ phẩm, trang thiết bị y tế không dùng trong phẫu thuật và thuốc không có số đăng ký, thuốc hỗ trợ điều trị;</li><li>Thời gian chờ;</li><li>Vật lý trị liệu: Bảo lãnh tối đa 5 lần. Sau 5 lần điều trị cần có sự đánh giá lại của bác sĩ theo mẫu Phiếu Theo Dõi Diễn Tiến Trị Liệu;</li><li><a href=\"/resources/files/exclusion_vi.pdf\">Danh sách loại trừ;</a></li></ul>', 'html', '', 6),
('fubon', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'Fubon', 'Fubon', 'FUBON VIETNAM', 'text', '', 4),
('gop_delete_interval', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'GOP Delete Interval (Days)', 'Thời gian cho phép xóa Bảo Lãnh (Ngày)', '1', 'integer', '', 8),
('is_smtp', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_smtp', 'Is SMTP', 'Là SMTP', 'Yes', 'enum', '[\"Yes\",\"No\"]', 5),
('majority_age', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_mobile_claim', 'Majority Age', 'Tuổi Trưởng Thành', '23', 'integer', '', 2),
('password_length', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_password', 'Password Length', 'Độ dài Mật Khẩu', '8', 'integer', '', 1),
('password_lowercase', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_password', 'Require Lowercase', 'Yêu cầu chữ nhỏ', 'No', 'enum', '[\"Yes\",\"No\"]', 2),
('password_number', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_password', 'Require Number', 'Yêu cầu số', 'No', 'enum', '[\"Yes\",\"No\"]', 4),
('password_symbol', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_password', 'Require Symbol', 'Yêu cầu ký tự đặc biệt', 'No', 'enum', '[\"Yes\",\"No\"]', 5),
('password_uppercase', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_password', 'Require Uppercase', 'Yêu Cầu chữ lớn', 'No', 'enum', '[\"Yes\",\"No\"]', 3),
('pcv', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'PCV', 'PCV', 'PACIFIC CROSS VIETNAM', 'text', '', 5),
('smtp_auth', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_smtp', 'SMTP Authetication', 'Xác thực SMTP', 'Yes', 'enum', '[\"Yes\",\"No\"]', 3),
('smtp_host', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_smtp', 'SMTP Host', 'Máy chủ SMTP', 'smtp.office365.com', 'text', '', 1),
('smtp_password', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_smtp', 'SMTP Password', 'Mật khẩu SMTP', 'Qos25754', 'password', '', 7),
('smtp_port', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_smtp', 'SMTP Port', 'Cổng SMTP', '587', 'integer', '', 2),
('smtp_secure', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_smtp', 'SMTP Secure', 'Bảo mật SMTP', 'TLS', 'enum', '[\"None\",\"SSL\",\"TLS\"]', 4),
('smtp_username', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_smtp', 'SMTP Username', 'Tên đăng nhập SMTP', 'inquiry@pacificcross.com.vn', 'text', '', 6),
('timezone', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_datetime', 'Time Zone', 'Múi Giờ', 'Asia/Ho_Chi_Minh', 'enum', '[\"Africa/Abidjan\",\"Africa/Accra\",\"Africa/Addis_Ababa\",\"Africa/Algiers\",\"Africa/Asmara\",\"Africa/Bamako\",\"Africa/Bangui\",\"Africa/Banjul\",\"Africa/Bissau\",\"Africa/Blantyre\",\"Africa/Brazzaville\",\"Africa/Bujumbura\",\"Africa/Cairo\",\"Africa/Casablanca\",\"Africa/Ceuta\",\"Africa/Conakry\",\"Africa/Dakar\",\"Africa/Dar_es_Salaam\",\"Africa/Djibouti\",\"Africa/Douala\",\"Africa/El_Aaiun\",\"Africa/Freetown\",\"Africa/Gaborone\",\"Africa/Harare\",\"Africa/Johannesburg\",\"Africa/Juba\",\"Africa/Kampala\",\"Africa/Khartoum\",\"Africa/Kigali\",\"Africa/Kinshasa\",\"Africa/Lagos\",\"Africa/Libreville\",\"Africa/Lome\",\"Africa/Luanda\",\"Africa/Lubumbashi\",\"Africa/Lusaka\",\"Africa/Malabo\",\"Africa/Maputo\",\"Africa/Maseru\",\"Africa/Mbabane\",\"Africa/Mogadishu\",\"Africa/Monrovia\",\"Africa/Nairobi\",\"Africa/Ndjamena\",\"Africa/Niamey\",\"Africa/Nouakchott\",\"Africa/Ouagadougou\",\"Africa/Porto-Novo\",\"Africa/Sao_Tome\",\"Africa/Tripoli\",\"Africa/Tunis\",\"Africa/Windhoek\",\"America/Adak\",\"America/Anchorage\",\"America/Anguilla\",\"America/Antigua\",\"America/Araguaina\",\"America/Argentina/Buenos_Aires\",\"America/Argentina/Catamarca\",\"America/Argentina/Cordoba\",\"America/Argentina/Jujuy\",\"America/Argentina/La_Rioja\",\"America/Argentina/Mendoza\",\"America/Argentina/Rio_Gallegos\",\"America/Argentina/Salta\",\"America/Argentina/San_Juan\",\"America/Argentina/San_Luis\",\"America/Argentina/Tucuman\",\"America/Argentina/Ushuaia\",\"America/Aruba\",\"America/Asuncion\",\"America/Atikokan\",\"America/Bahia\",\"America/Bahia_Banderas\",\"America/Barbados\",\"America/Belem\",\"America/Belize\",\"America/Blanc-Sablon\",\"America/Boa_Vista\",\"America/Bogota\",\"America/Boise\",\"America/Cambridge_Bay\",\"America/Campo_Grande\",\"America/Cancun\",\"America/Caracas\",\"America/Cayenne\",\"America/Cayman\",\"America/Chicago\",\"America/Chihuahua\",\"America/Costa_Rica\",\"America/Creston\",\"America/Cuiaba\",\"America/Curacao\",\"Datetime::getDatetimeFormat()America/Danmarkshavn\",\"America/Dawson\",\"America/Dawson_Creek\",\"America/Denver\",\"America/Detroit\",\"America/Dominica\",\"America/Edmonton\",\"America/Eirunepe\",\"America/El_Salvador\",\"America/Fort_Nelson\",\"America/Fortaleza\",\"America/Glace_Bay\",\"America/Godthab\",\"America/Goose_Bay\",\"America/Grand_Turk\",\"America/Grenada\",\"America/Guadeloupe\",\"America/Guatemala\",\"America/Guayaquil\",\"America/Guyana\",\"America/Halifax\",\"America/Havana\",\"America/Hermosillo\",\"America/Indiana/Indianapolis\",\"America/Indiana/Knox\",\"America/Indiana/Marengo\",\"America/Indiana/Petersburg\",\"America/Indiana/Tell_City\",\"America/Indiana/Vevay\",\"America/Indiana/Vincennes\",\"America/Indiana/Winamac\",\"America/Inuvik\",\"America/Iqaluit\",\"America/Jamaica\",\"America/Juneau\",\"America/Kentucky/Louisville\",\"America/Kentucky/Monticello\",\"America/Kralendijk\",\"America/La_Paz\",\"America/Lima\",\"America/Los_Angeles\",\"America/Lower_Princes\",\"America/Maceio\",\"America/Managua\",\"America/Manaus\",\"America/Marigot\",\"America/Martinique\",\"America/Matamoros\",\"America/Mazatlan\",\"America/Menominee\",\"America/Merida\",\"America/Metlakatla\",\"America/Mexico_City\",\"America/Miquelon\",\"America/Moncton\",\"America/Monterrey\",\"America/Montevideo\",\"America/Montserrat\",\"America/Nassau\",\"America/New_York\",\"America/Nipigon\",\"America/Nome\",\"America/Noronha\",\"America/North_Dakota/Beulah\",\"America/North_Dakota/Center\",\"America/North_Dakota/New_Salem\",\"America/Ojinaga\",\"America/Panama\",\"America/Pangnirtung\",\"America/Paramaribo\",\"America/Phoenix\",\"America/Port-au-Prince\",\"America/Port_of_Spain\",\"America/Porto_Velho\",\"America/Puerto_Rico\",\"America/Rainy_River\",\"America/Rankin_Inlet\",\"America/Recife\",\"America/Regina\",\"America/Resolute\",\"America/Rio_Branco\",\"America/Santarem\",\"America/Santiago\",\"America/Santo_Domingo\",\"America/Sao_Paulo\",\"America/Scoresbysund\",\"America/Sitka\",\"America/St_Barthelemy\",\"America/St_Johns\",\"America/St_Kitts\",\"America/St_Lucia\",\"America/St_Thomas\",\"America/St_Vincent\",\"America/Swift_Current\",\"America/Tegucigalpa\",\"America/Thule\",\"America/Thunder_Bay\",\"America/Tijuana\",\"America/Toronto\",\"America/Tortola\",\"America/Vancouver\",\"America/Whitehorse\",\"America/Winnipeg\",\"America/Yakutat\",\"America/Yellowknife\",\"Antarctica/Casey\",\"Antarctica/Davis\",\"Antarctica/DumontDUrville\",\"Antarctica/Macquarie\",\"Antarctica/Mawson\",\"Antarctica/McMurdo\",\"Antarctica/Palmer\",\"Antarctica/Rothera\",\"Antarctica/Syowa\",\"Antarctica/Troll\",\"Antarctica/Vostok\",\"Arctic/Longyearbyen\",\"Asia/Aden\",\"Asia/Almaty\",\"Asia/Amman\",\"Asia/Anadyr\",\"Asia/Aqtau\",\"Asia/Aqtobe\",\"Asia/Ashgabat\",\"Asia/Atyrau\",\"Asia/Baghdad\",\"Asia/Bahrain\",\"Asia/Baku\",\"Asia/Bangkok\",\"Asia/Barnaul\",\"Asia/Beirut\",\"Asia/Bishkek\",\"Asia/Brunei\",\"Asia/Chita\",\"Asia/Choibalsan\",\"Asia/Colombo\",\"Asia/Damascus\",\"Asia/Dhaka\",\"Asia/Dili\",\"Asia/Dubai\",\"Asia/Dushanbe\",\"Asia/Famagusta\",\"Asia/Gaza\",\"Asia/Hebron\",\"Asia/Ho_Chi_Minh\",\"Asia/Hong_Kong\",\"Asia/Hovd\",\"Asia/Irkutsk\",\"Asia/Jakarta\",\"Asia/Jayapura\",\"Asia/Jerusalem\",\"Asia/Kabul\",\"Asia/Kamchatka\",\"Asia/Karachi\",\"Asia/Kathmandu\",\"Asia/Khandyga\",\"Asia/Kolkata\",\"Asia/Krasnoyarsk\",\"Asia/Kuala_Lumpur\",\"Asia/Kuching\",\"Asia/Kuwait\",\"Asia/Macau\",\"Asia/Magadan\",\"Asia/Makassar\",\"Asia/Manila\",\"Asia/Muscat\",\"Asia/Nicosia\",\"Asia/Novokuznetsk\",\"Asia/Novosibirsk\",\"Asia/Omsk\",\"Asia/Oral\",\"Asia/Phnom_Penh\",\"Asia/Pontianak\",\"Asia/Pyongyang\",\"Asia/Qatar\",\"Asia/Qyzylorda\",\"Asia/Riyadh\",\"Asia/Sakhalin\",\"Asia/Samarkand\",\"Asia/Seoul\",\"Asia/Shanghai\",\"Asia/Singapore\",\"Asia/Srednekolymsk\",\"Asia/Taipei\",\"Asia/Tashkent\",\"Asia/Tbilisi\",\"Asia/Tehran\",\"Asia/Thimphu\",\"Asia/Tokyo\",\"Asia/Tomsk\",\"Asia/Ulaanbaatar\",\"Asia/Urumqi\",\"Asia/Ust-Nera\",\"Asia/Vientiane\",\"Asia/Vladivostok\",\"Asia/Yakutsk\",\"Asia/Yangon\",\"Asia/Yekaterinburg\",\"Asia/Yerevan\",\"Atlantic/Azores\",\"Atlantic/Bermuda\",\"Atlantic/Canary\",\"Atlantic/Cape_Verde\",\"Atlantic/Faroe\",\"Atlantic/Madeira\",\"Atlantic/Reykjavik\",\"Atlantic/South_Georgia\",\"Atlantic/St_Helena\",\"Atlantic/Stanley\",\"Australia/Adelaide\",\"Australia/Brisbane\",\"Australia/Broken_Hill\",\"Australia/Currie\",\"Australia/Darwin\",\"Australia/Eucla\",\"Australia/Hobart\",\"Australia/Lindeman\",\"Australia/Lord_Howe\",\"Australia/Melbourne\",\"Australia/Perth\",\"Australia/Sydney\",\"Europe/Amsterdam\",\"Europe/Andorra\",\"Europe/Astrakhan\",\"Europe/Athens\",\"Europe/Belgrade\",\"Europe/Berlin\",\"Europe/Bratislava\",\"Europe/Brussels\",\"Europe/Bucharest\",\"Europe/Budapest\",\"Europe/Busingen\",\"Europe/Chisinau\",\"Europe/Copenhagen\",\"Europe/Dublin\",\"Europe/Gibraltar\",\"Europe/Guernsey\",\"Europe/Helsinki\",\"Europe/Isle_of_Man\",\"Europe/Istanbul\",\"Europe/Jersey\",\"Europe/Kaliningrad\",\"Europe/Kiev\",\"Europe/Kirov\",\"Europe/Lisbon\",\"Europe/Ljubljana\",\"Europe/London\",\"Europe/Luxembourg\",\"Europe/Madrid\",\"Europe/Malta\",\"Europe/Mariehamn\",\"Europe/Minsk\",\"Europe/Monaco\",\"Europe/Moscow\",\"Europe/Oslo\",\"Europe/Paris\",\"Europe/Podgorica\",\"Europe/Prague\",\"Europe/Riga\",\"Europe/Rome\",\"Europe/Samara\",\"Europe/San_Marino\",\"Europe/Sarajevo\",\"Europe/Saratov\",\"Europe/Simferopol\",\"Europe/Skopje\",\"Europe/Sofia\",\"Europe/Stockholm\",\"Europe/Tallinn\",\"Europe/Tirane\",\"Europe/Ulyanovsk\",\"Europe/Uzhgorod\",\"Europe/Vaduz\",\"Europe/Vatican\",\"Europe/Vienna\",\"Europe/Vilnius\",\"Europe/Volgograd\",\"Europe/Warsaw\",\"Europe/Zagreb\",\"Europe/Zaporozhye\",\"Europe/Zurich\",\"Indian/Antananarivo\",\"Indian/Chagos\",\"Indian/Christmas\",\"Indian/Cocos\",\"Indian/Comoro\",\"Indian/Kerguelen\",\"Indian/Mahe\",\"Indian/Maldives\",\"Indian/Mauritius\",\"Indian/Mayotte\",\"Indian/Reunion\",\"Pacific/Apia\",\"Pacific/Auckland\",\"Pacific/Bougainville\",\"Pacific/Chatham\",\"Pacific/Chuuk\",\"Pacific/Easter\",\"Pacific/Efate\",\"Pacific/Enderbury\",\"Pacific/Fakaofo\",\"Pacific/Fiji\",\"Pacific/Funafuti\",\"Pacific/Galapagos\",\"Pacific/Gambier\",\"Pacific/Guadalcanal\",\"Pacific/Guam\",\"Pacific/Honolulu\",\"Pacific/Johnston\",\"Pacific/Kiritimati\",\"Pacific/Kosrae\",\"Pacific/Kwajalein\",\"Pacific/Majuro\",\"Pacific/Marquesas\",\"Pacific/Midway\",\"Pacific/Nauru\",\"Pacific/Niue\",\"Pacific/Norfolk\",\"Pacific/Noumea\",\"Pacific/Pago_Pago\",\"Pacific/Palau\",\"Pacific/Pitcairn\",\"Pacific/Pohnpei\",\"Pacific/Port_Moresby\",\"Pacific/Rarotonga\",\"Pacific/Saipan\",\"Pacific/Tahiti\",\"Pacific/Tarawa\",\"Pacific/Tongatapu\",\"Pacific/Wake\",\"Pacific/Wallis\"]', 3),
('update_time', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'setting_information', 'Update Time', 'Thời gian Cập nhật', '2020-12-31 09:01:08', 'datetime', '', 7);

--
-- Triggers `lzasetting`
--
DROP TRIGGER IF EXISTS `lzasetting__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzasetting__id` AFTER INSERT ON `lzasetting` FOR EACH ROW BEGIN SET @last_uuid = NEW.id; END
$$
DELIMITER ;

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
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzasms__id` BEFORE INSERT ON `lzasms` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzastatistic`
--

DROP TABLE IF EXISTS `lzastatistic`;
CREATE TABLE `lzastatistic` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzamodule_id` char(50) NOT NULL,
  `lzafield_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `name` varchar(200) NOT NULL,
  `conditions` varchar(500) NOT NULL,
  `extra` varchar(500) NOT NULL,
  `type` enum('Pie Chart','Horizontal Bar Chart','Vertical Bar Chart','Yearly Line Chart','Quarterly Line Chart','Monthly Line Chart','Weekly Line Chart','Daily Line Chart','Yearly Area Chart','Quarterly Area Chart','Monthly Area Chart','Weekly Area Chart','Daily Area Chart') NOT NULL,
  `width` enum('6','12') NOT NULL DEFAULT '12',
  `order_by` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzastatistic`
--

TRUNCATE TABLE `lzastatistic`;
--
-- Dumping data for table `lzastatistic`
--

INSERT INTO `lzastatistic` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzamodule_id`, `lzafield_id`, `user_id`, `name`, `conditions`, `extra`, `type`, `width`, `order_by`) VALUES
('c7d4a39a-4cee-11eb-bb4b-98fa9b10d0b1', NULL, '2021-01-02 11:36:35', NULL, NULL, 'user', '8f3c095b-4a64-11eb-a7cf-98fa9b10d0b1', '475a1daf-4bf3-11eb-8142-98fa9b10d0b1', 'User Role', '', '', 'Pie Chart', '6', 1);

--
-- Triggers `lzastatistic`
--
DROP TRIGGER IF EXISTS `lzastatistic__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzastatistic__id` BEFORE INSERT ON `lzastatistic` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzatask`
--

DROP TABLE IF EXISTS `lzatask`;
CREATE TABLE `lzatask` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL,
  `minute` varchar(255) NOT NULL,
  `hour` varchar(255) NOT NULL,
  `week_day` varchar(255) NOT NULL,
  `month_day` varchar(255) NOT NULL,
  `month` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `params` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzatask`
--

TRUNCATE TABLE `lzatask`;
--
-- Dumping data for table `lzatask`
--

INSERT INTO `lzatask` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `minute`, `hour`, `week_day`, `month_day`, `month`, `class`, `params`, `enabled`) VALUES
('885055bb-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Import PCV Data', '0', '*', '*', '*', '*', 'Lza\\App\\Task\\ImportDataTask', 'Pcv', 1),
('8851be10-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Import Fubon Data', '0', '*', '*', '*', '*', 'Lza\\App\\Task\\ImportDataTask', 'Fubon', 1),
('8851bef5-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Import Cathay Data', '0', '*', '*', '*', '*', 'Lza\\App\\Task\\ImportDataTask', 'Cathay', 1);

--
-- Triggers `lzatask`
--
DROP TRIGGER IF EXISTS `lzatask__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzatask__id` BEFORE INSERT ON `lzatask` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lzatext`
--

DROP TABLE IF EXISTS `lzatext`;
CREATE TABLE `lzatext` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `name` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `content_vi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzatext`
--

TRUNCATE TABLE `lzatext`;
--
-- Dumping data for table `lzatext`
--

INSERT INTO `lzatext` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `name`, `content`, `content_vi`) VALUES
('9fb33568-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Yes', 'Yes', 'Có'),
('9fb36bdc-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Deleted', 'Deleted', 'Đã xóa'),
('9fb36d18-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d/%m/%Y', '31/12/2000', '31/12/2000'),
('9fb36deb-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m/%d/%Y', '12/31/2000', '12/31/2000'),
('9fb36ee1-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y/%m/%d', '2000/12/31', '2000/12/31'),
('9fb36fb0-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d-%m-%Y', '31-12-2000', '31-12-2000'),
('9fb37065-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m-%d-%Y', '12-31-2000', '12-31-2000'),
('9fb37120-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y-%m-%d', '2000-12-31', '2000-12-31'),
('9fb371df-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d/%m/%Y %H:%i:%s', '31/12/2000 21:30:50', '31/12/2000 21:30:50'),
('9fb372a8-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m/%d/%Y %H:%i:%s', '12/31/2000 21:30:50', '12/31/2000 21:30:50'),
('9fb37372-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y/%m/%d %H:%i:%s', '2000/12/31 21:30:50', '2000/12/31 21:30:50'),
('9fb3742f-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'No', 'No', 'Không'),
('9fb374e3-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%d-%m-%Y %H:%i:%s', '31-12-2000 21:30:50', '31-12-2000 21:30:50'),
('9fb375ad-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%m-%d-%Y %H:%i:%s', '12-31-2000 21:30:50', '12-31-2000 21:30:50'),
('9fb37675-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '%Y-%m-%d %H:%i:%s', '2000-12-31 21:30:50', '2000-12-31 21:30:50'),
('9fb37747-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'None', 'None', 'Không có'),
('9fb37802-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Administrator', 'Administrator', 'Quản trị viên'),
('9fb378b8-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'User', 'User', 'Người dùng'),
('9fb3797a-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Customer Service', 'Customer Service', 'Chăm sóc Khách hàng'),
('9fb37a44-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Underwriter', 'Underwriter', 'Thẩm định viên'),
('9fb37af8-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Created', 'Created', 'Đã tạo'),
('9fb37bb9-4a63-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 'Updated', 'Updated', 'Đã sửa');

--
-- Triggers `lzatext`
--
DROP TRIGGER IF EXISTS `lzatext__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzatext__id` BEFORE INSERT ON `lzatext` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `lzauser`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `lzauser`;
CREATE TABLE `lzauser` (
`id` char(36)
,`crt_by` char(50)
,`crt_at` timestamp
,`upd_by` char(50)
,`upd_at` timestamp
,`lzarole_id` char(36)
,`provider_id` char(36)
,`username` varchar(50)
,`password` text
,`fullname` varchar(50)
,`email` varchar(200)
,`is_admin` enum('Yes','No')
,`notify` tinyint(1)
,`enabled` tinyint(1)
,`expiry` timestamp
,`last_reset_by` varchar(50)
,`last_reset_at` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `lzaview`
--

DROP TABLE IF EXISTS `lzaview`;
CREATE TABLE `lzaview` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzamodule_id` char(50) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `lzaview`
--

TRUNCATE TABLE `lzaview`;
--
-- Triggers `lzaview`
--
DROP TRIGGER IF EXISTS `lzaview__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `lzaview__id` BEFORE INSERT ON `lzaview` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim`
--

DROP TABLE IF EXISTS `mobile_claim`;
CREATE TABLE `mobile_claim` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mantis_id` int(10) UNSIGNED NOT NULL,
  `mobile_user_id` char(36) NOT NULL,
  `pay_type` varchar(30) NOT NULL,
  `pres_amt` int(10) UNSIGNED NOT NULL,
  `mobile_user_bank_account_id` char(36) DEFAULT NULL,
  `mobile_claim_status_id` char(36) NOT NULL DEFAULT 'fb01ff6b-4a6b-11eb-a7cf-98fa9b10d0b1',
  `reason` varchar(30) DEFAULT NULL,
  `symtom_time` datetime DEFAULT NULL,
  `occur_time` datetime DEFAULT NULL,
  `body_part` varchar(300) DEFAULT NULL,
  `incident_detail` text DEFAULT NULL,
  `note` text NOT NULL,
  `dependent_memb_no` varchar(20) DEFAULT NULL,
  `fullname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim`
--

TRUNCATE TABLE `mobile_claim`;
--
-- Triggers `mobile_claim`
--
DROP TRIGGER IF EXISTS `mobile_claim__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_claim__id` BEFORE INSERT ON `mobile_claim` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim_file`
--

DROP TABLE IF EXISTS `mobile_claim_file`;
CREATE TABLE `mobile_claim_file` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mobile_claim_id` char(36) NOT NULL,
  `note` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filetype` varchar(50) NOT NULL,
  `filesize` int(10) UNSIGNED NOT NULL,
  `checksum` char(32) NOT NULL,
  `contents` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim_file`
--

TRUNCATE TABLE `mobile_claim_file`;
--
-- Triggers `mobile_claim_file`
--
DROP TRIGGER IF EXISTS `mobile_claim_file__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_claim_file__id` BEFORE INSERT ON `mobile_claim_file` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim_otp`
--

DROP TABLE IF EXISTS `mobile_claim_otp`;
CREATE TABLE `mobile_claim_otp` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` char(20) NOT NULL,
  `otp` char(6) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim_otp`
--

TRUNCATE TABLE `mobile_claim_otp`;
--
-- Triggers `mobile_claim_otp`
--
DROP TRIGGER IF EXISTS `mobile_claim_otp__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_claim_otp__id` BEFORE INSERT ON `mobile_claim_otp` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_claim_status`
--

DROP TABLE IF EXISTS `mobile_claim_status`;
CREATE TABLE `mobile_claim_status` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `code` int(3) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `name_vi` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_claim_status`
--

TRUNCATE TABLE `mobile_claim_status`;
--
-- Dumping data for table `mobile_claim_status`
--

INSERT INTO `mobile_claim_status` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `code`, `name`, `name_vi`) VALUES
('fb01ff6b-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 10, 'New', 'Mới'),
('fb0377c2-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 11, 'Accepted', 'Chấp Nhận'),
('fb037b49-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 12, 'Partially Accepted', 'Chấp Nhận Một Phần'),
('fb03ea27-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 13, 'Declined', 'Từ Chối'),
('fb03ee1b-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 16, 'Info Request', 'Yêu Cầu Thông Tin'),
('c0b850b9-4ff7-11eb-ba33-000d3a821253', 'admin', '2021-01-06 08:19:24', NULL, NULL, 17, 'Info Submitted', 'Đã Nhận Thông Tin'),
('fb043d22-4a6b-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, 18, 'Ready For Process', 'Sẵn Sàng Xủ Lý');

--
-- Triggers `mobile_claim_status`
--
DROP TRIGGER IF EXISTS `mobile_claim_status__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_claim_status__id` BEFORE INSERT ON `mobile_claim_status` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_device`
--

DROP TABLE IF EXISTS `mobile_device`;
CREATE TABLE `mobile_device` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mobile_user_id` char(36) NOT NULL,
  `device_token` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_device`
--

TRUNCATE TABLE `mobile_device`;
--
-- Triggers `mobile_device`
--
DROP TRIGGER IF EXISTS `mobile_device__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_device__id` BEFORE INSERT ON `mobile_device` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_user`
--

DROP TABLE IF EXISTS `mobile_user`;
CREATE TABLE `mobile_user` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `pocy_no` varchar(50) NOT NULL DEFAULT '',
  `mbr_no` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `address` varchar(200) NOT NULL,
  `photo` longtext DEFAULT NULL,
  `tel` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `language` enum('','_vi') NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user`
--

TRUNCATE TABLE `mobile_user`;
--
-- Triggers `mobile_user`
--
DROP TRIGGER IF EXISTS `mobile_user__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_user__id` BEFORE INSERT ON `mobile_user` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_user_bank_account`
--

DROP TABLE IF EXISTS `mobile_user_bank_account`;
CREATE TABLE `mobile_user_bank_account` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mobile_user_id` char(36) NOT NULL,
  `bank_name` varchar(250) NOT NULL,
  `bank_address` varchar(250) NOT NULL,
  `bank_acc_no` varchar(50) NOT NULL,
  `bank_acc_name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user_bank_account`
--

TRUNCATE TABLE `mobile_user_bank_account`;
--
-- Triggers `mobile_user_bank_account`
--
DROP TRIGGER IF EXISTS `mobile_user_bank_account__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_user_bank_account__id` BEFORE INSERT ON `mobile_user_bank_account` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_user_reset_password`
--

DROP TABLE IF EXISTS `mobile_user_reset_password`;
CREATE TABLE `mobile_user_reset_password` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(200) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user_reset_password`
--

TRUNCATE TABLE `mobile_user_reset_password`;
--
-- Triggers `mobile_user_reset_password`
--
DROP TRIGGER IF EXISTS `mobile_user_reset_password__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_user_reset_password__id` BEFORE INSERT ON `mobile_user_reset_password` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_user_session`
--

DROP TABLE IF EXISTS `mobile_user_session`;
CREATE TABLE `mobile_user_session` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` char(20) NOT NULL,
  `token` char(50) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `mobile_user_session`
--

TRUNCATE TABLE `mobile_user_session`;
--
-- Triggers `mobile_user_session`
--
DROP TRIGGER IF EXISTS `mobile_user_session__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `mobile_user_session__id` BEFORE INSERT ON `mobile_user_session` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_benefit`
--

DROP TABLE IF EXISTS `pcv_benefit`;
CREATE TABLE `pcv_benefit` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `parent` char(36) DEFAULT NULL,
  `pcv_head_id` char(36) NOT NULL,
  `ben_type` varchar(10) NOT NULL,
  `ben_desc` varchar(500) NOT NULL,
  `ben_desc_vi` varchar(500) NOT NULL,
  `ben_note` varchar(500) NOT NULL,
  `ben_note_vi` varchar(500) NOT NULL,
  `gender` enum('M','F','B') NOT NULL,
  `is_combined` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_gop` tinyint(1) UNSIGNED DEFAULT NULL,
  `no_first_year` enum('Y','N') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_benefit`
--

TRUNCATE TABLE `pcv_benefit`;
--
-- Dumping data for table `pcv_benefit`
--

INSERT INTO `pcv_benefit` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `parent`, `pcv_head_id`, `ben_type`, `ben_desc`, `ben_desc_vi`, `ben_note`, `ben_note_vi`, `gender`, `is_combined`, `is_gop`, `no_first_year`) VALUES
('408579ff-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad45f76-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'In-patient Treatment', 'Điều trị nội trú', '', '', 'B', 1, 0, 'Y'),
('4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad58c1d-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Mandatory miscarriage or abortion as prescribed by doctor', 'Sảy thai hoặc phá thai bắt buộc theo chỉ định của bác sĩ', '(90 days waiting)', '(90 ngày chờ)', 'F', 1, 1, 'Y'),
('4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad5906f-4a6f-11eb-a7cf-98fa9b10d0b1', 'DT', 'Dental Treatment', 'Điều trị răng', '(co-payment 80-20)', '(đồng thanh toán 80-20)', 'B', 1, 1, 'Y'),
('4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad52614-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'Pre & Post Hospital Visit', 'Thăm khám trước & sau khi nhập viện', '', '', 'B', 1, 1, 'Y'),
('4087639f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad59b47-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'Outpatient Surgery', 'Phẫu thuật ngoại trú', '', '', 'B', 1, 1, 'Y'),
('4087670f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad5b553-4a6f-11eb-a7cf-98fa9b10d0b1', 'IP', 'Emergency due to Accident', 'Cấp cứu do tai nạn', '', '', 'B', 1, 1, 'Y'),
('408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, NULL, '1ad5d3a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', '', '', 'B', 1, 0, 'Y'),
('40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad5d3a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Out-patient Treatment', 'Điều trị ngoại trú', '', '', 'B', 1, 1, 'Y'),
('40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad559ee-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Alternative Medicines', 'Y học thay thế', '', '', 'B', 1, 1, 'Y'),
('40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad57878-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Medical Checkup', 'Khám tổng quát định kỳ hàng năm', '', '', 'B', 1, 0, 'Y'),
('408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:46', NULL, NULL, '408769c3-4a6f-11eb-a7cf-98fa9b10d0b1', '1ad5814e-4a6f-11eb-a7cf-98fa9b10d0b1', 'OP', 'Maternity', 'Quyền lợi thai sản', '(including antenatal care & pregnancy related drugs, GOP is only applied after 12-month waiting period)', '(bao gồm khám thai & thuốc liên quan đến thai sản, chỉ áp dụng bảo lãnh sau 12 tháng chờ)', 'F', 1, 1, 'Y');

--
-- Triggers `pcv_benefit`
--
DROP TRIGGER IF EXISTS `pcv_benefit__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_benefit__id` BEFORE INSERT ON `pcv_benefit` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_benefit_provider`
--

DROP TABLE IF EXISTS `pcv_benefit_provider`;
CREATE TABLE `pcv_benefit_provider` (
  `pcv_benefit_id` char(36) NOT NULL,
  `provider_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_benefit_provider`
--

TRUNCATE TABLE `pcv_benefit_provider`;
--
-- Dumping data for table `pcv_benefit_provider`
--

INSERT INTO `pcv_benefit_provider` (`pcv_benefit_id`, `provider_id`) VALUES
('408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d5f17-4bf0-11eb-8142-98fa9b10d0b1'),
('408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d7fc1-4bf0-11eb-8142-98fa9b10d0b1'),
('4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d5f17-4bf0-11eb-8142-98fa9b10d0b1'),
('4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d7fc1-4bf0-11eb-8142-98fa9b10d0b1');

-- --------------------------------------------------------

--
-- Table structure for table `pcv_claim_line`
--

DROP TABLE IF EXISTS `pcv_claim_line`;
CREATE TABLE `pcv_claim_line` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `cl_no` varchar(20) NOT NULL,
  `db_ref_no` varchar(30) DEFAULT NULL,
  `incur_date_from` date NOT NULL,
  `ben_head` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `prov_name` varchar(255) DEFAULT NULL,
  `pres_amt` double UNSIGNED DEFAULT NULL,
  `app_amt` double UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_claim_line`
--

TRUNCATE TABLE `pcv_claim_line`;
--
-- Triggers `pcv_claim_line`
--
DROP TRIGGER IF EXISTS `pcv_claim_line__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_claim_line__id` BEFORE INSERT ON `pcv_claim_line` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_claim_line2`
--

DROP TABLE IF EXISTS `pcv_claim_line2`;
CREATE TABLE `pcv_claim_line2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_no` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `cl_no` varchar(20) NOT NULL,
  `db_ref_no` varchar(30) DEFAULT NULL,
  `incur_date_from` date NOT NULL,
  `ben_head` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `prov_name` varchar(255) DEFAULT NULL,
  `pres_amt` double UNSIGNED DEFAULT NULL,
  `app_amt` double UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_claim_line2`
--

TRUNCATE TABLE `pcv_claim_line2`;
--
-- Triggers `pcv_claim_line2`
--
DROP TRIGGER IF EXISTS `pcv_claim_line2__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_claim_line2__id` BEFORE INSERT ON `pcv_claim_line2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_db_claim`
--

DROP TABLE IF EXISTS `pcv_db_claim`;
CREATE TABLE `pcv_db_claim` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `db_ref_no` varchar(50) DEFAULT NULL,
  `pcv_history_id` char(36) NOT NULL,
  `pcv_head_id` char(36) NOT NULL,
  `pres_amt` int(10) UNSIGNED NOT NULL,
  `app_amt` int(10) UNSIGNED NOT NULL,
  `status` enum('Pending','Confirmed','Canceled','Deleted','Accepted','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_db_claim`
--

TRUNCATE TABLE `pcv_db_claim`;
--
-- Triggers `pcv_db_claim`
--
DROP TRIGGER IF EXISTS `pcv_db_claim__ai`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_db_claim__ai` AFTER INSERT ON `pcv_db_claim` FOR EACH ROW INSERT INTO pcv_db_claim_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM pcv_db_claim AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `pcv_db_claim__au`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_db_claim__au` AFTER UPDATE ON `pcv_db_claim` FOR EACH ROW BEGIN
	DECLARE new_id char(36);
	SET new_id = NEW.id;
	UPDATE pcv_db_claim_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = new_id AND d.valid_to IS NULL;
	INSERT INTO pcv_db_claim_history
	SELECT 'Updated', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM pcv_db_claim AS d
	WHERE d.id = new_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `pcv_db_claim__bd`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_db_claim__bd` BEFORE DELETE ON `pcv_db_claim` FOR EACH ROW BEGIN
	DECLARE old_id char(36);
	SET old_id = OLD.id;
	UPDATE pcv_db_claim_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = old_id AND d.valid_to IS NULL;
	INSERT INTO pcv_db_claim_history
	SELECT 'Deleted', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM pcv_db_claim AS d
	WHERE d.id = old_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `pcv_db_claim__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_db_claim__id` BEFORE INSERT ON `pcv_db_claim` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_db_claim_history`
--

DROP TABLE IF EXISTS `pcv_db_claim_history`;
CREATE TABLE `pcv_db_claim_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `db_ref_no` varchar(50) DEFAULT NULL,
  `pcv_history_id` char(36) DEFAULT NULL,
  `pcv_head_id` char(36) DEFAULT NULL,
  `pres_amt` int(10) UNSIGNED DEFAULT NULL,
  `app_amt` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Confirmed','Canceled','Deleted','Accepted','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_db_claim_history`
--

TRUNCATE TABLE `pcv_db_claim_history`;
-- --------------------------------------------------------

--
-- Table structure for table `pcv_head`
--

DROP TABLE IF EXISTS `pcv_head`;
CREATE TABLE `pcv_head` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `pcv_benefit_id` char(36) NOT NULL,
  `code` varchar(10) NOT NULL,
  `ben_heads` varchar(100) NOT NULL,
  `name` varchar(500) NOT NULL,
  `name_vi` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_head`
--

TRUNCATE TABLE `pcv_head`;
--
-- Dumping data for table `pcv_head`
--

INSERT INTO `pcv_head` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `pcv_benefit_id`, `code`, `ben_heads`, `name`, `name_vi`) VALUES
('1ad45f76-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408579ff-4a6f-11eb-a7cf-98fa9b10d0b1', 'IPALL', 'IP', 'Inpatient Treatment', 'Điều trị Nội Trú'),
('1ad4a581-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'CMED', 'CMED', 'Chinese Prescribed Medicine', 'Chi phí Thuốc theo toa của Trung Quốc'),
('1ad4a908-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'HOV', 'HOV', 'Home doctor visit', 'Chi phí Thăm Khám tại nhà'),
('1ad4abe3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'HYNO', 'HYNO', 'Hypnotherapist', 'Chi phí Chuyên gia Thôi Miên'),
('1ad514d4-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'LAB', 'LAB', 'Laboratory Charges', 'Chi phí Xét nghiệm'),
('1ad517c3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISC', 'MISC', 'Miscellaneous Charges - covered', 'Chi phí y tế điều trị trong ngày được chi trả'),
('1ad51aed-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISN', 'MISN', 'Miscellaneous Charges - not cover', 'Chi phí y tế điều trị trong ngày không được chi trả'),
('1ad51d17-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'OV', 'OV', 'Office Visit', 'Phí bác sĩ'),
('1ad51f30-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'OVRX', 'OVRX', 'Consultation & medicine', 'Chi phí khám chữa bệnh'),
('1ad52142-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'PHYS', 'PHYS', 'Physiotherapist', 'Chi phí vật lý trị liệu'),
('1ad52404-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSYM', 'PSYM', 'Psychiatric & mental illnesses', 'Điều trị Bệnh Tâm Thần'),
('1ad52614-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'HVALL', 'PORX, POSH', 'Pre & Post Hospital Visit', 'Thăm khám trước & sau khi nhập viện'),
('1ad52828-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'RPFE', 'RPFE', 'Reprice Fee', 'Phí Thanh toán lại'),
('1ad52ae5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'RX', 'RX', 'Prescribed Medicine', 'Chi phí thuốc theo toa'),
('1ad52cef-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SP', 'SP', 'Specialist consultation', 'Chi phí Tư vấn Chuyên môn'),
('1ad52ef6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SPOR', 'SPOR', 'Special Sport Cover', 'Chi phí Bảo Hiểm cho Thể Thao Đặc Biệt'),
('1ad531b2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SUPP', 'SUPP', 'Supplies', 'Chi phí Vật Tư'),
('1ad552f6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'SURAP', 'SURAP', 'Surgical appliances', 'Chi phí Dụng cụ phẫu thuật'),
('1ad5572f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'XRAY', 'XRAY', 'X-Ray', 'Chi phí chụp x-quang'),
('1ad559ee-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'AMALL', 'ACUP, BSET, CGP, HERB, HLIS, HMEO, OSTE', 'Alternative Medicines', 'Chi phí Y Học Thay Thế'),
('1ad55d6c-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'ACUP', 'ACUP', 'Acupuncture', 'Chi phí Châm cứu'),
('1ad5600e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'BSET', 'BSET', 'Bone Setter', 'Chi phí Nắn xương'),
('1ad5636e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'PORX', 'PORX', 'Pre Hospital Visit', 'Thăm khám trước khi nhập viện'),
('1ad566d4-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'CGP', 'CGP', 'Chinese Practitioner Consultation', 'Chi phí Tư Vấn Chuyên Gia Trung Quốc'),
('1ad56a3c-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'HERB', 'HERB', 'Prescribed herbs', 'Chi phí Thảo dược kê đơn'),
('1ad56d90-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'HLIS', 'HLIS', 'Herbalist', 'Chi phí Thảo dược'),
('1ad573fc-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'HMEO', 'HMEO', 'Homeopathic treatment', 'Điều trị vi lượng đồng căn'),
('1ad57643-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876ec2-4a6f-11eb-a7cf-98fa9b10d0b1', 'OSTE', 'OSTE', 'Osteopathy', 'Điều trị Loãng xương'),
('1ad57878-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'MEDCALL', 'MEDC, VACI', 'Medical Checkup', 'Kiểm tra Y tế'),
('1ad57aaf-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'MEDC', 'MEDC', 'Medical Checkup', 'Kiểm tra Y tế'),
('1ad57ce0-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40877135-4a6f-11eb-a7cf-98fa9b10d0b1', 'VACI', 'VACI', 'Vaccine', 'Vắc Xin'),
('1ad57f20-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'MAT', 'MAT', 'Maternity', 'Khám thai'),
('1ad5814e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'DELIALL', 'DELI, MAT', 'Normal Delivery', 'Sinh thường'),
('1ad5837b-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086f2a5-4a6f-11eb-a7cf-98fa9b10d0b1', 'POSH', 'POSH', 'Post Hospital Visit', 'Thăm khám sau khi nhập viện'),
('1ad585a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'DELI', 'DELI', 'Normal Delivery', 'Sinh thường'),
('1ad587d1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'CXPALL', 'CXP, MAT', 'Surgical Delivery', 'Sinh Mổ'),
('1ad589f8-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'CXP', 'CXP', 'Surgical Delivery', 'Sinh Mổ'),
('1ad58c1d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MCARALL', 'MAT, MCAR', 'Miscarriage/Abortion', 'Sẩy/Bỏ Thai'),
('1ad58e46-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MCAR', 'MCAR', 'Miscarriage/Abortion', 'Sẩy/Bỏ Thai'),
('1ad5906f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DTALL', 'DT', 'Dental Treatment', 'Đều trị răng'),
('1ad59293-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ABS', 'ABS', 'Abscess w/o Surgery', 'Áp xe không phẫu thuật'),
('1ad594b6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ABSS', 'ABSS', 'Abscess with Surgery', 'Áp xe có phẫu thuật'),
('1ad596d9-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ADEN', 'ADEN', 'Treatment for dental accident', 'Điều trị tai biến nha khoa'),
('1ad59917-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'AE', 'AE', 'Anterior Teeth with Acid Etch', 'Răng trước có khắc axit'),
('1ad59b47-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4087639f-4a6f-11eb-a7cf-98fa9b10d0b1', 'IMIS', 'IMIS', 'Outpatient Surgery', 'Phẫu thuật ngoại trú'),
('1ad59d6b-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'APAT', 'APAT', 'Apicoetomy Anterior Teeth', 'Phẫu thuật nhổ bỏ răng trước'),
('1ad59f8d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'APMP', 'APMP', 'Apicoetomy Molar & Pre-Molar', 'Nhổ răng hàm và răng tiền hàm'),
('1ad5a1b6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'BR', 'BR', 'Bridge Per Unit', 'Bắc cầu trên mỗi đơn vị'),
('1ad5a3e2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'COMP', 'COMP', 'Anterior Teeth', 'Răng trước'),
('1ad5a66c-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'CR', 'CR', 'Crown Per Tooth', 'Niềng từng răng'),
('1ad5a8d5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'CSTI', 'CSTI', 'Complete Soft Tissue or Bony Impaction', 'Hoàn chỉnh Mô Mềm hoặc Tác động Xương'),
('1ad5ab4b-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DBUL', 'DBUL', 'Denture Upper & Lower', 'Hàm giả trên & dưới'),
('1ad5adb0-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DEPP', 'DEPP', 'Denture Partial Plate', 'Bộ phận răng giả'),
('1ad5b069-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DEPT', 'DEPT', 'Denture Partial Each Tooth', 'Làm từng chiếc răng giả'),
('1ad5b2e1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DENT', 'DENT', 'General out patient dental benefits', 'Quyền lợi điều trị nha khoa'),
('1ad5b553-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4087670f-4a6f-11eb-a7cf-98fa9b10d0b1', 'ER', 'ER', 'Emergency Room', 'Chi phí Phòng Cấp Cứu'),
('1ad5b783-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'DUOL', 'DUOL', 'Denture Upper or Lower', 'Hàm giả trên hoặc dưới'),
('1ad5b9b7-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ETN', 'ETN', 'ER Normal Hr', 'ER Normal Hr'),
('1ad5bbe3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ETO', 'ETO', 'ER Outside Normal Hr', 'ER Outside Normal Hr'),
('1ad5be12-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'EXT', 'EXT', 'Extraction - Uncomplicated', 'Chiết xuất - Không phức tạp'),
('1ad5c0a1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'EXTI', 'EXTI', 'Extraction - Impacted Wisdom Teeth', 'Nhổ - Răng khôn bị ảnh hưởng'),
('1ad5c413-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GI1S', 'GI1S', 'Gold Inlay 1st Surface', 'Bề mặt thứ 1 dát vàng'),
('1ad5c7e2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GI2S', 'GI2S', 'Gold Inlay 2nd Surface', 'Bề mặt thứ 2 dát vàng'),
('1ad5cb5f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GI3S', 'GI3S', 'Gold Inlay 3rd Surface', 'Bề mặt thứ 3 dát vàng'),
('1ad5cf6d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR1', 'GPR1', '1st Gold Pin for Cusp Restoration', 'Chốt vàng thứ 1 để phục hồi múi'),
('1ad5d189-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR2', 'GPR2', '2nd Gold Pin for Cusp Restoration', 'Chốt vàng thứ 2 để phục hồi múi'),
('1ad5d3a3-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'OPALL', 'OP', 'Outpatient Treatment', 'Điều trị Ngoại Trú'),
('1ad5d5c5-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR3', 'GPR3', '3rd Gold Pin for Cusp Restoration', 'Chốt vàng thứ 3 để phục hồi múi'),
('1ad5d7da-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'GPR4', 'GPR4', '4th Gold Pin for Cusp Restoration', 'Chốt vàng thứ 4 để phục hồi múi'),
('1ad5d9f1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MF1S', 'MF1S', 'Molar & Pre-molar Filling 1st Surface', 'Làm đầy bề mặt răng hàm và răng tiền hàm trên thứ nhất'),
('1ad5dc17-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MF2S', 'MF2S', 'Molar & Pre-molar Filling 2nd Surface', 'Làm đầy bề mặt răng hàm và răng tiền hàm trên thứ 2'),
('1ad5de3e-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MFPT', 'MFPT', 'Molar Filling Per Tooth', 'Làm đầy răng hàm trên mỗi răng'),
('1ad5e058-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISC', 'MISC', 'Covered misc charges', 'Các khoản phí khác được bảo hiểm'),
('1ad5e26d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'MISN', 'MISN', 'Charges is not covered', 'Các khoản phí không được bảo hiểm'),
('1ad5e484-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'OE1', 'OE1', 'Oral Examination', 'Quyền lợi kiểm tra răng miệng 1'),
('1ad6004a-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'OE2', 'OE2', 'Oral Examination', 'Quyền lợi kiểm tra răng miệng 2'),
('1ad60441-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'ORTH', 'ORTH', 'Orthodontic Treatment Per Year', 'Điều trị chỉnh nha mỗi năm'),
('1ad606de-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'CHEMO', 'CHEMO', 'Oncology (chemotherapy)', 'Điều trị Ung thư (hóa trị liệu)'),
('1ad6094a-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'OR17', 'OR17', 'Orthodontic treatment : children up to 17', 'Điều trị chỉnh nha: trẻ em đến 17 tuổi'),
('1ad60ba6-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSGQ', 'PSGQ', 'Periodontal Gingivectomy Per Quadrant (include Post OP Visit)', 'Cắt nướu nha chu cho mỗi phần tư (bao gồm Tái khám cho bệnh nhân ngoại trú)'),
('1ad60e40-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS1T', 'PS1T', '1st Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 1'),
('1ad6107d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS2T', 'PS2T', '2nd Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 2'),
('1ad612ab-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS3T', 'PS3T', '3rd Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 3'),
('1ad614d2-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS4T', 'PS4T', '4th Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 4'),
('1ad616fa-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS5T', 'PS5T', '5th Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 5'),
('1ad61915-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PS6T', 'PS6T', '6th Tooth Periodontal Gingivectomy', 'Cắt nướu nha chu răng thứ 6'),
('1ad61b4f-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSSC', 'PSSC', 'Periodontal Subgingival Curretage Per Treatment', 'Nạo răng dưới nướu cho mỗi lần điều trị'),
('1ad61d76-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'PSTI', 'PSTI', 'Partial Soft Tissue Impaction', 'Lực ép một phần lên mô mềm'),
('1ad62087-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '40876c49-4a6f-11eb-a7cf-98fa9b10d0b1', 'CHIR', 'CHIR', 'Chiropractor', 'Điều trị bệnh về Chân'),
('1ad622b1-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF1', 'RCF1', '1st Root Canal', 'Ống tủy thứ 1'),
('1ad62505-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF2', 'RCF2', '2nd Root Canal', 'Ống tủy thứ 2'),
('1ad62723-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF3', 'RCF3', '3rd Root Canal', 'Ống tủy thứ 3'),
('1ad62959-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'RCF4', 'RCF4', '4th Root Canal', 'Ống tủy thứ 4'),
('1ad62b78-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'XR1', 'XR1', '1ST X-Ray', 'X-Ray lần 1'),
('1ad62d9d-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'XR2', 'XR2', 'Each Additional Film', 'Mỗi phim bổ sung'),
('1ad62fbb-4a6f-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, '4086ef10-4a6f-11eb-a7cf-98fa9b10d0b1', 'XRPA', 'XRPA', 'Panoramic', 'Chụp toàn hàm');

--
-- Triggers `pcv_head`
--
DROP TRIGGER IF EXISTS `pcv_head__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_head__id` BEFORE INSERT ON `pcv_head` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_history`
--

DROP TABLE IF EXISTS `pcv_history`;
CREATE TABLE `pcv_history` (
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
-- Truncate table before insert `pcv_history`
--

TRUNCATE TABLE `pcv_history`;
--
-- Triggers `pcv_history`
--
DROP TRIGGER IF EXISTS `pcv_history__ai`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_history__ai` AFTER INSERT ON `pcv_history` FOR EACH ROW INSERT INTO pcv_history_history
	SELECT 'Created', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM pcv_history AS d
	WHERE d.id = NEW.id
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `pcv_history__au`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_history__au` AFTER UPDATE ON `pcv_history` FOR EACH ROW BEGIN
	DECLARE new_id char(36);
	SET new_id = NEW.id;
	UPDATE pcv_history_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = new_id AND d.valid_to IS NULL;
	INSERT INTO pcv_history_history
	SELECT 'Updated', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM pcv_history AS d
	WHERE d.id = new_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `pcv_history__bd`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_history__bd` BEFORE DELETE ON `pcv_history` FOR EACH ROW BEGIN
	DECLARE old_id char(36);
	SET old_id = OLD.id;
	UPDATE pcv_history_history d
	SET d.valid_to = CURRENT_TIMESTAMP(6)
	WHERE d.id = old_id AND d.valid_to IS NULL;
	INSERT INTO pcv_history_history
	SELECT 'Deleted', CURRENT_TIMESTAMP(6), NULL, d.*
	FROM pcv_history AS d
	WHERE d.id = old_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `pcv_history__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_history__id` BEFORE INSERT ON `pcv_history` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_history_history`
--

DROP TABLE IF EXISTS `pcv_history_history`;
CREATE TABLE `pcv_history_history` (
  `action` enum('Created','Updated','Deleted') DEFAULT 'Created',
  `valid_from` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `valid_to` timestamp(6) NULL DEFAULT NULL,
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT NULL,
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL,
  `mantis_id` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(30) DEFAULT NULL,
  `time` datetime(6) NOT NULL,
  `pocy_no` varchar(50) DEFAULT NULL,
  `mbr_no` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `provider_id` char(36) DEFAULT NULL,
  `incur_date` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_history_history`
--

TRUNCATE TABLE `pcv_history_history`;
-- --------------------------------------------------------

--
-- Table structure for table `pcv_member`
--

DROP TABLE IF EXISTS `pcv_member`;
CREATE TABLE `pcv_member` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `mbr_name_en` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('M','F') NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `mepl_oid` int(11) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `min_memb_eff_date` date NOT NULL,
  `min_pocy_eff_date` date NOT NULL,
  `insured_periods` varchar(500) NOT NULL,
  `wait_period` enum('Yes','No') NOT NULL,
  `spec_dis_period` enum('Yes','No') NOT NULL,
  `product` varchar(10) NOT NULL,
  `plan_desc` varchar(255) NOT NULL,
  `memb_rstr` text DEFAULT NULL,
  `memb_rstr_vi` text DEFAULT NULL,
  `primary_broker_name` varchar(255) NOT NULL,
  `broker_name` varchar(255) DEFAULT NULL,
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `ip_limit` double DEFAULT NULL,
  `op_copay_pct` int(3) UNSIGNED DEFAULT NULL,
  `op_limit_per_year` double DEFAULT NULL,
  `op_limit_per_visit` double DEFAULT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `dt_ind` enum('Yes','No') NOT NULL,
  `am_limit_per_year` double DEFAULT NULL,
  `os_limit_per_year` double DEFAULT NULL,
  `dt_limit_per_year` double DEFAULT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes',
  `ben_schedule` varchar(10000) DEFAULT NULL,
  `benefit_en` text DEFAULT NULL,
  `benefit_vi` text DEFAULT NULL,
  `children` varchar(4000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_member`
--

TRUNCATE TABLE `pcv_member`;
-- --------------------------------------------------------

--
-- Table structure for table `pcv_member2`
--

DROP TABLE IF EXISTS `pcv_member2`;
CREATE TABLE `pcv_member2` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `mbr_name` varchar(255) NOT NULL,
  `mbr_name_en` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('M','F') NOT NULL,
  `pocy_no` varchar(20) NOT NULL,
  `pocy_ref_no` varchar(30) DEFAULT NULL,
  `mbr_no` varchar(20) NOT NULL,
  `mepl_oid` int(11) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `memb_eff_date` date NOT NULL,
  `memb_exp_date` date NOT NULL,
  `term_date` date DEFAULT NULL,
  `min_memb_eff_date` date NOT NULL,
  `min_pocy_eff_date` date NOT NULL,
  `insured_periods` varchar(500) NOT NULL,
  `wait_period` enum('Yes','No') NOT NULL,
  `spec_dis_period` enum('Yes','No') NOT NULL,
  `product` varchar(10) NOT NULL,
  `plan_desc` varchar(255) NOT NULL,
  `memb_rstr` text DEFAULT NULL,
  `memb_rstr_vi` text DEFAULT NULL,
  `primary_broker_name` varchar(255) NOT NULL,
  `broker_name` varchar(255) DEFAULT NULL,
  `reinst_date` date DEFAULT NULL,
  `policy_status` varchar(255) DEFAULT NULL,
  `is_renew` enum('Yes','No') NOT NULL,
  `ip_limit` double DEFAULT NULL,
  `op_copay_pct` int(3) UNSIGNED DEFAULT NULL,
  `op_limit_per_year` double DEFAULT NULL,
  `op_limit_per_visit` double DEFAULT NULL,
  `op_ind` enum('Yes','No') NOT NULL,
  `dt_ind` enum('Yes','No') NOT NULL,
  `am_limit_per_year` double DEFAULT NULL,
  `os_limit_per_year` double DEFAULT NULL,
  `dt_limit_per_year` double DEFAULT NULL,
  `has_op_debit_note` enum('Yes','No') DEFAULT 'Yes',
  `ben_schedule` varchar(10000) DEFAULT NULL,
  `benefit_en` text DEFAULT NULL,
  `benefit_vi` text DEFAULT NULL,
  `children` varchar(4000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `pcv_member2`
--

TRUNCATE TABLE `pcv_member2`;
--
-- Triggers `pcv_member2`
--
DROP TRIGGER IF EXISTS `pcv_member2__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_member2__id` BEFORE INSERT ON `pcv_member2` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pcv_plan_desc_map`
--

DROP TABLE IF EXISTS `pcv_plan_desc_map`;
CREATE TABLE `pcv_plan_desc_map` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `crt_by` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `haystack` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `needle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_by` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `pcv_plan_desc_map`
--

TRUNCATE TABLE `pcv_plan_desc_map`;
--
-- Dumping data for table `pcv_plan_desc_map`
--

INSERT INTO `pcv_plan_desc_map` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `haystack`, `needle`, `order_by`) VALUES
('665d3f00-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EM1', 'EMERGENCY 1', 1),
('665d56ff-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EM2', 'EMERGENCY 2', 2),
('665d5c09-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EM3', 'EMERGENCY 3', 3),
('665d6fad-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'M1', 'MASTER M1+', 4),
('665e184a-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'M2', 'MASTER M2', 5),
('665e1ba3-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'M3', 'MASTER M3', 6),
('665e1e12-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'SMASTER', 'SENIOR', 7),
('665e206b-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'SENIOR M1+', 'SENIOR M1', 8),
('665e22bd-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'FS (STD)', 'FOUNDATION (STANDARD)', 9),
('665e2516-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'FS (EXE)', 'FOUNDATION (EXECUTIVE)', 10),
('665e275a-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'FS (PRM)', 'FOUNDATION (PREMIER)', 11),
('665e2992-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'OP (STD)', 'OUTPATIENT (STANDARD)', 12),
('665e2bcc-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'OP (EXE)', 'OUTPATIENT (EXECUTIVE)', 13),
('665e2e06-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'OP (PRM)', 'OUTPATIENT (PREMIER)', 14),
('665e3040-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' OP', ' OUTPATIENT', 15),
('665e3278-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'STD,', 'STANDARD,', 16),
('665e34b1-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'EXE,', 'EXECUTIVE,', 17),
('665e370d-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PRM,', 'PREMIER,', 18),
('665e3947-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'TK', ' TAKE-OVER', 19),
('665e3b80-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'WO/', 'WITHOUT ', 20),
('665e3db9-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/ O', 'W/O', 21),
('665e3ff2-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O OP', 'WITHOUT OUTPATIENT', 22),
('665e4227-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O DT', 'WITHOUT DENTAL', 23),
('665e445c-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O PA', 'WITHOUT PERSONAL ACCIDENT', 24),
('665e4694-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'W/O ', 'WITHOUT ', 25),
('665e48ca-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'DT1', 'DENTAL 1', 26),
('665e4b02-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'DT2', 'DENTAL 2', 27),
('665e4d36-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'DT3', 'DENTAL 3', 28),
('665e4f6c-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' DT', ' DENTAL', 29),
('665e51a6-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PA1', 'PERSONAL ACCIDENT 1', 30),
('665e53e1-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PA2', 'PERSONAL ACCIDENT 2', 31),
('665e561a-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'PA3', 'PERSONAL ACCIDENT 3', 32),
('665e5856-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' PA', ' PERSONAL ACCIDENT', 33),
('665e5a8e-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' CO-PAY', ' CO-PAYMENT', 34),
('665e5ce2-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' DED', ' DEDUCTIBLE', 35),
('665e5f1b-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' TR', ' TRAVEL', 36),
('665e614f-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' RB', ' ROOM & BOARD', 37),
('665e6388-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' TAL', ' TREATMENT AREA LIMIT', 38),
('665e65c2-4a6e-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, ' SUR', ' SURGERY', 39);

--
-- Triggers `pcv_plan_desc_map`
--
DROP TRIGGER IF EXISTS `pcv_plan_desc_map__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `pcv_plan_desc_map__id` BEFORE INSERT ON `pcv_plan_desc_map` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `slug` varchar(200) NOT NULL,
  `metatitle` varchar(200) DEFAULT NULL,
  `metadescription` varchar(200) DEFAULT NULL,
  `metakeyword` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `post`
--

TRUNCATE TABLE `post`;
--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `slug`, `metatitle`, `metadescription`, `metakeyword`, `content`, `enabled`) VALUES
('1397b1e3-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'api', 'PCV Card Validation API', 'PCV Card Validation API Page', 'pcv,card validation,api', '', 1),
('1397f2d1-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'home', 'Welcome to PCV Card Validation', 'PCV Card Validation Home Page', 'pcv,card validation,home', '', 1),
('1397fa65-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'login', 'PCV Card Validation - Login', 'PCV Card Validation Login Page', 'pcv,card validation,login', '', 1),
('1397ffa2-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'change-password', 'PCV Card Validation - Change Password', 'PCV Card Validation Change Password Page', 'pcv,card validation,change password', '', 1),
('1398079a-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'forget-password', 'PCV Card Validation - Forget Password', 'PCV Card Validation Forget Password Page', 'pcv,card validation,forget password', '', 1),
('13980f11-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'reset-password', 'PCV Card Validation - Reset Password', 'PCV Card Validation Reset Password Page', 'pcv,card validation,reset password', '', 1),
('1398146d-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'pcv-details', 'PCV Card Validation - Details', 'PCV Card Validation Details Page', 'pcv,card validation,tpa', '', 1),
('13981d32-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'pcv-gop-list', 'PCV Card Validation - GOP List', 'PCV Card Validation - GOP List Page', 'pcv,card validation,gop,list', '', 1),
('139823b8-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'fubon-details', 'Fubon Card Validation - Details', 'Fubon Card Validation Details Page', 'Fubon,card validation,tpa', '', 1),
('13982771-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'fubon-gop-list', 'Fubon Card Validation - GOP List', 'Fubon Card Validation - GOP List Page', 'Fubon,card validation,gop,list', '', 1),
('13982afa-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'cathay-details', 'Cathay Card Validation - Details', 'Cathay Card Validation Details Page', 'cathay,card validation,tpa', '', 1),
('13982e81-4a69-11eb-a7cf-98fa9b10d0b1', 'admin', '2020-12-31 02:02:47', NULL, NULL, 'cathay-gop-list', 'Cathay Card Validation - GOP List', 'Cathay Card Validation - GOP List Page', 'cathay,card validation,gop,list', '', 1);

--
-- Triggers `post`
--
DROP TRIGGER IF EXISTS `post__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `post__id` BEFORE INSERT ON `post` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `provider`
--

DROP TABLE IF EXISTS `provider`;
CREATE TABLE `provider` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `provider`
--

TRUNCATE TABLE `provider`;
--
-- Dumping data for table `provider`
--

INSERT INTO `provider` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `code`, `name`) VALUES
('fd2be414-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '', 'Pacific Cross Vietnam'),
('fd2d5f17-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000002', 'FV Hospital'),
('fd2d634e-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000054', 'Vinmec International Hospital'),
('fd2d66a6-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000067', 'Hong Ngoc General Hospital'),
('fd2d6890-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000066', 'City International Hospital'),
('fd2d6a73-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000020', 'Columbia Asia Gia Dinh Hospital'),
('fd2d6d71-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000056', 'Columbia Asia Binh Duong Hospital'),
('fd2d6f7e-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000065', 'Saigon Ent Hospital'),
('fd2d71b5-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000070', 'Thu Cuc International General Hospital'),
('fd2d73b8-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000045', 'Hoan My Saigon General Hospital Jsc'),
('fd2d7583-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000091', 'Vinmec Central Park Hospital'),
('fd2d77a3-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000006', 'Family Medical Practice Ha Noi'),
('fd2d79c7-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000005', 'Family Medical Practice HCMC'),
('fd2d7bf9-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000188', 'Tan Hung General Hospital (Dist 7)'),
('fd2d7e08-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000069', 'Van Hanh General Hospital'),
('fd2d7fc1-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000052', 'Hanh Phuc Hospital'),
('fd2d81d0-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000063', 'Hoan My Da Nang General Hospital'),
('fd2d83e5-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000078', 'Hung Viet Cancer Hospital'),
('fd2d868a-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000080', 'Tam Tri Danang General Hospital'),
('fd2d8794-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000072', 'Tri Duc Hospital'),
('fd2d8884-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000051', 'Anh Minh International Hospital'),
('fd2d895e-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000111', 'Vinmec Phu Quoc Hospital'),
('fd2d8a47-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000129', 'Tam Tri Saigon General Hospital'),
('fd2d8b24-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000115', 'Tam Tri Nha Trang General Hospital'),
('fd2d8bfd-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000108', 'Vinmec Nha Trang International General Hospital'),
('fd2d8ce7-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000116', 'Family Hospital'),
('fd2d8ddf-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000142', 'Vinmec Da Nang International Hospital'),
('fd2d8ecb-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000139', 'Vinmec Hai Phong International Hospital'),
('fd2d8fb0-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000189', 'Van Phuc Hospital'),
('fd2d9098-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000187', 'Hai Phong Medical University Hospital'),
('fd2d9188-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000184', 'Binh Dinh General Hospital'),
('fd2d92d5-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000031', 'Da Nang Hospital'),
('fd2d93c8-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000143', 'American International Hospital (AIH)'),
('fd2d94ba-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000024', 'Family Medical Practice Da Nang'),
('fd2d959a-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000008', 'Victoria Healthcare My My Jsc'),
('fd2d9676-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000058', 'American Chiropractice Clinic (ACC)'),
('fd2d974f-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000121', 'Careplus Clinic Viet Nam'),
('fd2d9832-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000088', 'Phuong Chau International Hospital'),
('fd2d990e-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000133', 'Minh Anh International Hospital'),
('fd2d99e7-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000044', 'Hoan My Cuu Long General Hospital Jsc'),
('fd2d9aca-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000046', 'Hoan My Dalat General Hospital'),
('fd2d9ba2-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000190', 'Hoan My Minh Hai General Hospital'),
('fd2d9c83-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000094', 'Hospital 22-12 (VK Hospital)'),
('fd2d9d60-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000098', 'Vinh International Hospital'),
('fd2d9e44-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000101', 'Hong Duc General Hospital III'),
('fd2d9f1f-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000096', 'Nhat Tan Hospital Company Limited'),
('fd2d9ffc-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000106', 'Sai Gon Binh Duong Hospital'),
('fd2da0db-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000104', 'Dong Do Hospital Joint Stock Company'),
('fd2da1ba-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000113', 'Medlatec General Hospital'),
('fd2da29a-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000119', 'Cao Thang International Eye Hospital'),
('fd2da37e-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000117', 'Diamond Clinic'),
('fd2da464-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000010', 'Centre Médical International '),
('fd2da53c-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000120', 'Hung Vuong General Hospital'),
('fd2da61c-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000125', 'Vinmec Ha Long International Hospital'),
('fd2da701-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '', 'Maple Healthcare'),
('fd2da7eb-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000079', 'Medelab Clinic'),
('fd2da8cb-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000191', 'Medical Care International '),
('fd2da9b0-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000084', 'Phuc Khang General Clinic'),
('fd2daa90-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000055', 'Stamford Medical Clinic'),
('fd2dab78-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000034', 'Vietsing Clinic'),
('fd2dac5d-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000183', 'Bac Ha International General Hospital'),
('fd2dad40-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000053', 'Vigor Anbis Japan'),
('fd2dae21-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000050', 'Viet Gia Clinic'),
('fd2daf02-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000060', 'Yersin International Clinic '),
('fd2dafe4-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000036', 'European Medical Center'),
('fd2db0c2-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000192', 'Medical University Clinic'),
('fd2db9e0-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000100', 'Children''s healthy care clinic'),
('fd2dbc71-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '', 'Maple Healthcare Center'),
('fd2dbe7b-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000093', 'Vung Tau General Clinic'),
('fd2dc023-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000099', 'Nguyen An Phuc General Clinic'),
('fd2dc1a2-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000105', 'Monaco Healthcare Company Limited'),
('fd2dc324-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000110', 'Viet My Clinic'),
('fd2dc599-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000193', 'Medlatec Clinic'),
('fd2dc751-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000194', ' Pacific Clinic'),
('fd2dc853-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000196', 'The International Pavilion at Hue National Hospital'),
('fd2dc940-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000124', 'An Viet General Hospital'),
('fd2dca29-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000134', 'Benh Vien Da Khoa Tu Nhan Ha Thanh'),
('fd2dcb05-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000195', 'Phuong Dong General Hospital'),
('fd2dcbdf-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000122', 'Nguyen Minh Hong Hospital'),
('fd2dccbe-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000103', 'Hong Phuc Hospital Joint Stock Company'),
('fd2dcda1-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000186', 'Hano Medical Joint Stock Company'),
('fd2dce7b-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000130', 'Tam Anh General Hospital'),
('fd2dcf57-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000074', 'Dong Nai International Hospital'),
('fd2dd031-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000083', 'Hai Phong International Hospital'),
('fd2dd104-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000061', 'Ngoc Minh Genral Clinic'),
('fd2dd1fe-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000003', 'Columbia Asia Sai Gon Clinic'),
('fd2dd2dc-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000185', 'Gia An 115 Hospital'),
('fd2dd3be-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000201', 'Nam Sai Gon International Hospital'),
('fd2dd496-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000255', 'Medic-BD General Hospital'),
('fd2dd575-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000256', 'DOLIFE INTERNATIONAL HOSPITAL'),
('fd2dd64f-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000257', 'Hop Luc Clinical Hospital'),
('fd2dd72b-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000460', 'Hoan My ITO Dong Nai'),
('fd2dd811-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000259', 'Binh Dan General Hospital CO., LTD'),
('fd2dd8e9-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000087', 'American Chiropractice Clinic (ACC) - Ha Noi'),
('fd2dd9d6-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000386', 'American Chiropractice Clinic (ACC) - Da Nang'),
('fd2ddabc-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000279', 'Phu Tho Province General Hospital'),
('fd2ddb9a-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000387', 'Thien Nhan Da Nang Hospital'),
('fd2ddc71-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000388', 'Duc Khang Hospital'),
('fd2ddd53-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000389', 'Sai Gon-ITO Hospital joint stock company'),
('fd2dde37-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000391', 'Hoa Binh General Hospital Joint Stock Company'),
('fd2ddf16-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000390', 'Obstetric &amp; Pediatric Center - Phu Tho Province General Hospital'),
('fd2de093-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000356', 'LacViet Friendship Hospital'),
('fd2de1a9-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000392', 'Vinh Duc General Hospital Join Stock Company'),
('fd2de291-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000393', 'DND International Eye Hospital'),
('fd2de370-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000394', 'DND dental clinic'),
('fd2de451-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000395', 'An Thinh Obstetrics &amp; Gynecology Hospital'),
('fd2df8fc-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000396', 'Hi Dental clinic'),
('fd2dfb18-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000397', 'Bee Kids Clinic Company Limited'),
('fd2dfc0c-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000398', 'Thu Cuc International General Clinic'),
('fd2dfcea-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000457', 'Thai Nguyen International Hospital'),
('fd2dfdcb-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000458', 'Tam Tri Dong Thap Hospital'),
('fd2dfeb3-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000462', 'Hong Ngoc 2 General Clinic (Hong Ngoc Ha Dong) '),
('fd2dff9e-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000459', 'Hong Ngoc 3 General Clinic (Hong Ngoc Nguyen Tuan) '),
('fd2e0081-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000441', 'Medical DIAG Center'),
('fd2e016c-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000432', 'Cuoc Song General Hospital'),
('fd2e024a-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000461', 'Quang Ninh Province General Hospital'),
('fd2e0329-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000011', 'L’Hospital Francais De Hanoi'),
('fd2e03f9-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000475', 'Shing Mark Medical University Hospital'),
('fd2e04ec-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000027', 'An Sinh Hospital'),
('fd2e05cf-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000478', 'Hoan My Binh Phuoc General Hospital'),
('fd2e06ad-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000490', 'Hoa Hao - Medic Can Tho General Hospital'),
('fd2e0796-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '514', 'Le Loi Hospital'),
('fd2e08dd-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '511', 'Thai Anh General Clinic'),
('fd2e09c8-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000335', 'Thien Duc International General Hospital'),
('fd2e0ab0-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '519', 'Ho Chi Minh Medical University Hospital'),
('fd2e0b8e-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000520', '74 Central Hospital'),
('fd2e0c72-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000519', ''),
('fd2e0d50-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '562', 'Quoc Anh Hospital'),
('fd2e0e33-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '561', 'Thanh Chan International General Clinic'),
('fd2e0f0c-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '560', 'Viet Han General Clinic'),
('fd2e0ff0-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '559', 'Thanh Ha General Hospital'),
('fd2e10ce-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '558', 'Olympus Gia My Clinic'),
('fd2e1911-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '553', 'Sa Pa General Hospital'),
('fd2e1a03-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000579', 'Phyathai Nawamin Hospital'),
('fd2e1ada-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000580', 'Nha Navii Company limited - Hoa Ma Branch'),
('fd2e1bc1-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000581', 'Nha Navii Company limited - Cua Dong Branch'),
('fd2e1cb1-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000582', 'Australian Dental Clinic'),
('fd2e1d95-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000583', 'Hoan My International Hospital (Bac Ninh)'),
('fd2e1e7c-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000591', 'Jio Health Smart Clinic'),
('fd2e1f5f-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000416', 'Hien Duc Obstetrics And Gynecology Clinic'),
('fd2e204b-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000656', 'Van Phuoc Cuu Long General Clinic'),
('fd2e212d-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000657', 'Hospital of Vietnam National University, Hanoi - 182 Luong The Vinh Clinic'),
('fd2e2272-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000658', 'Binh Duong Private General Hospital'),
('fd2e2357-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000659', 'Duc Minh General Hospital'),
('fd2e2448-4bf0-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:46:55', NULL, NULL, '000660', 'Van Phuc 2 General Hospital');

--
-- Triggers `provider`
--
DROP TRIGGER IF EXISTS `provider__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `provider__id` BEFORE INSERT ON `provider` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lzarole_id` char(36) NOT NULL,
  `provider_id` char(36) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `email` varchar(200) NOT NULL,
  `is_admin` enum('Yes','No') NOT NULL DEFAULT 'No',
  `notify` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `expiry` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_reset_by` varchar(50) DEFAULT NULL,
  `last_reset_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `user`
--

TRUNCATE TABLE `user`;
--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzarole_id`, `provider_id`, `username`, `password`, `fullname`, `email`, `is_admin`, `notify`, `enabled`, `expiry`, `last_reset_by`, `last_reset_at`) VALUES
('475a1daf-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'admin', '2f834994eac9d76786609fa12dcac6f2edb6ba1731615df1c9b4e4e5cadc71a0', 'Administrator', 'admin@bluecross.com.vn', 'Yes', 1, 1, '2029-12-30 20:00:00', NULL, NULL),
('475a87e5-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thaotran', '1520205de098aec2463ec43fb3745ccaddd4d467316bd631b110f41e6e31e094', 'Thao Tran', 'thaotran@pacificcross.com.vn', 'No', 1, 1, '2021-01-23 19:32:30', NULL, NULL),
('475a90c2-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thile', '46a59e581e9ad046e7c5166f2898ceda9becf30c8e532f6cc76bc22708e527d4', 'Thi Le', 'thile@pacificcross.com.vn', 'No', 1, 1, '2019-01-29 05:26:38', NULL, NULL),
('475abb32-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thuannguyen', '01b7fdf2bacf268a1a52ed5dd45973e6b6bf14362f95a659b6f7d92388468412', 'Thuan Nguyen', 'thuannguyen@pacificcross.com.vn', 'No', 1, 1, '2019-01-29 05:20:34', NULL, NULL),
('475b9f9d-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'phuongnguyen', 'ce006d935f4d287a212a88d378bc4b968ab966da127945444b5c16ac6af49865', 'Phuong Nguyen', 'phuong.nguyen@pacificcross.com.vn', 'No', 1, 1, '2019-04-21 11:28:54', NULL, NULL),
('475ba77d-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'ngocnguyen', '5a22e286539f822ae2e5dd20ae52fa56c604eec25bba3f650f6816602f7c5618', 'Nguyễn Bảo Ngọc', 'ngocnguyen@pacificcross.com.vn', 'No', 1, 1, '2021-06-16 01:21:09', NULL, NULL),
('475bad0a-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'nhiha', '710bd39535a6f30fba1ab732f7343f79768f7f53eaade10e08b5cb706b9348f6', 'Nhi Ha', 'nhiha@pacificcross.com.vn', 'No', 1, 1, '2019-10-04 06:14:42', NULL, NULL),
('475bb610-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'hanhvan', '9d6bee0f6b9a598ddf3d99568a8c100b9f8efa7d0d4010b8302dbdbb9fd64933', 'Hanh Van', 'hanhvan@pacificcross.com.vn', 'No', 1, 1, '2021-02-13 14:11:52', NULL, NULL),
('475bbdd3-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'tienngo', '23410944cafd8527f92507566095304608e777790da52774e51574d6e37c3901', 'Tien', 'tienngo@pacificcross.com.vn', 'Yes', 1, 1, '2020-09-15 16:48:46', NULL, NULL),
('475bc1eb-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'nghiemle', '484b4ad372d5ce7c93f7b1005a9e162173a19446dca6bf9c8fa5ffa09957984b', 'Lê Vĩnh Nghiêm', 'nghiemle@pacificcross.com.vn', 'No', 1, 1, '2020-08-17 15:54:26', NULL, NULL),
('475bc5a3-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'adamstevens', '49d045a78e043568e5730776c39e08d96a17e92eba514b08cc66d99ae24a3062', 'Adam Stevens', 'adamstevens@pacificcross.com.vn', 'No', 1, 1, '2020-10-23 14:08:09', NULL, NULL),
('475bc92b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thanhdang', '4277da34970953687525ebe16fd1f3f152d4031335987488e4b411fc5f7426fe', 'Đặng Lê Hồng Thanh', 'thanhdang@pacificcross.com.vn', 'Yes', 1, 1, '2020-12-10 17:19:20', NULL, NULL),
('475bccc3-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e6887d6-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'camnguyen', 'b879d7141d26a5476380716a2826cc63db4926275338ce58cda8afc64117454b', 'Nguyễn Thị Hồng Cẩm', 'camnguyen@pacificcross.com.vn', 'No', 1, 1, '2019-07-06 12:32:49', NULL, NULL),
('475bd035-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'trangluong', '07362cff7d7f29697c7dcc2f1b20ce5a21eab6c9e3ac97fdb2848c433b48be1d', 'Lương Thị Thùy Trang', 'trangluong@pacificcross.com.vn', 'No', 1, 1, '2019-06-04 04:59:33', NULL, NULL),
('475bd3d4-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'anhca', '8229376d6ad1b388d4ae5a3a8bfc96fdfd1fe912fdd286d28d315f83293851b6', 'Ca Thị Quỳnh Anh', 'anhca@pacificcross.com.vn', 'No', 1, 1, '2020-11-13 13:22:56', NULL, NULL),
('475bd7f5-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'huongnguyen', '769fa31ab16bbf13106cdcc3004247ed43a68f13f8da4af342ebf64327974d13', 'Nguyễn Việt Hương', 'huongnguyen@pacificcross.com.vn', 'No', 1, 1, '2021-02-06 12:11:37', NULL, NULL),
('475bdbbd-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'ngocpham', 'bc2f86b1a6a8396be792a6626b9290915bfa411b7056344a3bd4be2971caf8ea', 'Ngọc Phạm', 'ngocpham@pacificcross.com.vn', 'No', 1, 1, '2019-06-04 05:30:07', NULL, NULL),
('475bdf8e-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'hangmai', '0ce400a72d6ba46e3d2090b7a45254ddd63e4dd9aea39b6690dbeb750b924fa8', 'Mai Bích Hằng', 'hangmai@pacificcross.com.vn', 'No', 1, 1, '2021-06-30 05:03:01', NULL, NULL),
('475be7f9-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'tuyenhong', 'e1f79c1cf351841eeb1db82d4239ffaf514bdb5211b08be2d75b5a5bc6e1f0a7', 'Hồng Ngọc Tuyền', 'tuyenhong@pacificcross.com.vn', 'No', 1, 1, '2021-05-24 04:45:52', NULL, NULL),
('475bf174-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'truongngo', '115d90fb16843c5b346a3214a78848ee465dc4c4b8a1432d7f07ca51f78ad84f', 'Ngô Thị Trường', 'truongngo@pacificcross.com.vn', 'No', 1, 1, '2019-06-27 09:13:12', NULL, NULL),
('475bf985-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'Dunghuynh', 'f3cec9670523542f8dd0576e89ede09e24350073943336d2d17a7616ee4bfef0', 'Huynh Le Thuy Dung', 'dunghuynh@pacificcross.com.vn', 'No', 0, 0, '2019-01-01 06:04:45', NULL, NULL),
('475c06db-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'Hangnguyen', '475597e5110ea8bd4862168fd81af47e96523bcc45bbc7e3f87161f7136de7db', 'Nguyen Thuy Hang', 'hangthuynguyen@pacificcross.com.vn', 'No', 0, 0, '2019-01-01 06:09:26', NULL, NULL),
('475c0f7c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d5f17-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@fvhospital.com', 'f096ba49f90af56de50e01fd7d7721ef92e4940a9ea89f8c65748bcde9464006', 'FV Hospital', 'insurance@fvhospital.com', 'No', 0, 0, '2021-01-21 18:39:26', NULL, NULL),
('475c1769-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d66a6-4bf0-11eb-8142-98fa9b10d0b1', 'baohiem@hongngochospital.vn', '91c17c32e0d78b989150bdffda90d7c95488e9531b1a48244c4b00375bfbea42', 'Hong Ngoc Hospital', 'baohiem@hongngochospital.vn', 'No', 0, 0, '2019-01-02 05:05:21', NULL, NULL),
('475c1e30-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d6890-4bf0-11eb-8142-98fa9b10d0b1', 'directbilling@cih.com.vn', '7719aca48b9a4cb176d7cdaa01017d523611a15c0fa65e01c6f379156862024d', 'City International Hospital', 'directbilling@cih.com.vn', 'No', 0, 0, '2019-07-02 05:14:23', NULL, NULL),
('475c2541-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d6a73-4bf0-11eb-8142-98fa9b10d0b1', 'insurance.giadinh@columbiaasia.com', '238300aa7ca680caa6149090c7e7b05cda8654d7223aa55ca20f34bad758f961', 'Columbia Asia Gia Dinh Hospital', 'insurance.giadinh@columbiaasia.com', 'No', 1, 1, '2019-09-03 12:39:20', NULL, NULL),
('475c2c19-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d6d71-4bf0-11eb-8142-98fa9b10d0b1', 'insurance.binhduong@columbiaasia.com', '12bbbc9455906d979ae8dc1bcb1df334568f7f4bbffd6cd516aa775f07ead4d9', 'Columbia Asia Binh Duong Hospital', 'insurance.binhduong@columbiaasia.com', 'No', 0, 0, '2019-01-02 05:19:03', NULL, NULL),
('475c3364-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d6f7e-4bf0-11eb-8142-98fa9b10d0b1', 'baohiem@taimuihongsg.com', '8fc952271cdb975905f8b07c5c6aa6705681070fcf29391b0ead0ba30d2ce64f', 'Ears Nose Throat SG Hospital (ENT)', 'baohiem@taimuihongsg.com', 'No', 0, 0, '2019-07-03 13:14:42', NULL, NULL),
('475c39ea-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d71b5-4bf0-11eb-8142-98fa9b10d0b1', 'baohiem@thucuchospital.vn', 'b2962c86bdc139cecd86e8b584802b3a1d137d8042ca719df5a6e32c674a4c70', 'Thu Cuc International Hospital', 'baohiem@thucuchospital.vn', 'No', 0, 0, '2019-07-03 09:14:53', NULL, NULL),
('475c404b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d634e-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@vinmec.com', 'cf70f97ba78531d7f0679fcd2ca368e5a9078fbbaf841e9b4bc77127d9a9983b', 'Vinmec Times City Hospital', 'insurance@vinmec.com', 'No', 0, 0, '2019-01-02 06:03:40', NULL, NULL),
('475c46d4-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d73b8-4bf0-11eb-8142-98fa9b10d0b1', 'hmsg.blvp@hoanmy.com', 'da6a2ff39bf23e8c6b9c63250e3242d8d566067dfa6fd54ba7fad15a5edb9617', 'Hoan My Sai Gon Hospital', 'hmsg.blvp@hoanmy.com', 'No', 0, 0, '2021-03-11 13:01:15', NULL, NULL),
('475c4dd8-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d7583-4bf0-11eb-8142-98fa9b10d0b1', 'insurance.cp@vinmec.com', 'b780fae691bab50f3bde0ed85e509a9b9e9216145e592f0f445d33ba8924cec4', 'Vinmec Central Park Hospital', 'insurance.cp@vinmec.com', 'No', 0, 0, '2019-07-09 15:43:09', NULL, NULL),
('475c556b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d77a3-4bf0-11eb-8142-98fa9b10d0b1', 'hanoi@vietnammedicalpractice.com', '64aa3f4d0f99fe1e559c168ae6f4cccb5df97eae490dfc7d438fdca814d42202', 'Vietnam Medical Practice Ha Noi', 'hanoi@vietnammedicalpractice.com', 'No', 1, 1, '2020-11-27 19:15:11', NULL, NULL),
('475c5b6c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d79c7-4bf0-11eb-8142-98fa9b10d0b1', 'hcmc@vietnammedicalpractice.com', '9fb0c9b83267d511b99c931a4751d9af124f7bd4a447e675b6f0768ff5926052', 'Vietnam Medical Practice HCMC - Binh Thanh', 'hcmc@vietnammedicalpractice.com', 'No', 0, 0, '2020-11-29 13:07:31', NULL, NULL),
('475c6188-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d7bf9-4bf0-11eb-8142-98fa9b10d0b1', 'baohiem@benhvientanhung.com', 'c2ab90a03c8dc6170b8d3b442fa5ac84cc1bd849a68fb6f7544877bc6fec0310', 'Tan Hung Hospital', 'baohiem@benhvientanhung.com', 'No', 0, 0, '2021-04-26 11:29:01', NULL, NULL),
('475c68b5-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d7e08-4bf0-11eb-8142-98fa9b10d0b1', 'benhvienvanhanh@gmail.com', '7db3c73e9ce03dc2ad69402304f238b7c115066c51b85b88c091f7cfbc36783c', 'Van Hanh Hospital', 'benhvienvanhanh@gmail.com', 'No', 0, 0, '2019-01-02 06:40:31', NULL, NULL),
('475c701c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d7fc1-4bf0-11eb-8142-98fa9b10d0b1', 'Insteam@hanhphuchospital.com', '9ef24e5d9eefaa0b409dd0e9e03edd7fcb1c684c2509f4aef18b656656854960', 'Hanh Phuc Hospital', 'Insteam@hanhphuchospital.com', 'No', 1, 1, '2021-04-26 19:12:07', NULL, NULL),
('475c782a-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d81d0-4bf0-11eb-8142-98fa9b10d0b1', 'hmdn.insurance@hoanmy.com', '76393108b5c3cc8b3352421a9fead4c0d805662a85dea956da1cd506bce7aa4a', 'Hoan My Da Nang Hospital', 'hmdn.insurance@hoanmy.com', 'No', 0, 0, '2019-07-02 07:03:44', NULL, NULL),
('475c7fb5-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d83e5-4bf0-11eb-8142-98fa9b10d0b1', 'cskh.bvhv@gmail.com', 'bf18595f4c31090b0f2153d7bac2a7992eb3ebc6b584595c0f5030e7db112339', 'Hung Viet Hospital', 'cskh.bvhv@gmail.com', 'No', 0, 0, '2019-01-02 06:58:28', NULL, NULL),
('475c870c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d868a-4bf0-11eb-8142-98fa9b10d0b1', 'insurance.danang@tmmchealthcare.com', 'f4953543983de719c7183c49f8f6ddbab1b3dbbd5298d293ffed5e3111daaca4', 'Tam Tri Da Nang Hospital', 'insurance.danang@tmmchealthcare.com', 'No', 0, 0, '2019-01-02 07:00:38', NULL, NULL),
('475c8e90-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8794-4bf0-11eb-8142-98fa9b10d0b1', 'baohiemtriduc@gmail.com', 'aefe75294ba09828fe4ba34c7d238216bb0ba6dd09c2336c17e986e9f3025010', 'Tri Duc Hospital', 'baohiemtriduc@gmail.com', 'No', 0, 0, '2019-01-02 07:03:56', NULL, NULL),
('475c95b9-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8884-4bf0-11eb-8142-98fa9b10d0b1', 'baolanhvienphi@vuanhhospital.com.vn', 'ded774e9fff06ee82b6c2eff95e77eeac967d1b35152c28848a12c2c7fd160da', 'Vu Anh International Hospital', 'baolanhvienphi@vuanhhospital.com.vn', 'No', 0, 0, '2019-01-02 07:06:41', NULL, NULL),
('475c9d6c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d895e-4bf0-11eb-8142-98fa9b10d0b1', 'v.insurance.pq@vinmec.com', '25e3e2aad503a6ea6db7e6f29c930af58db2896ff078686ad4ec6cd528b9edb8', 'Vinmec Phu Quoc Hospital', 'v.insurance.pq@vinmec.com', 'No', 0, 0, '2019-10-04 07:49:16', NULL, NULL),
('475ca49b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8a47-4bf0-11eb-8142-98fa9b10d0b1', 'pac@tmmchealthcare.com', '2b4b4c9e4082167ee2675feb206352addd19a70107fd8eb212e1b19621749152', 'Tam Tri Sai Gon Hospital', 'pac@tmmchealthcare.com', 'No', 0, 0, '2019-01-02 07:18:51', NULL, NULL),
('475cabba-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8b24-4bf0-11eb-8142-98fa9b10d0b1', 'pac.nt@tmmchealthcare.com', '2cf675c9895ec34e52562b7d3119c8faaa7eebce826553f9e9ca90db032465a2', 'Tam Tri Nha Trang Hospital', 'pac.nt@tmmchealthcare.com', 'No', 0, 0, '2019-01-02 07:19:42', NULL, NULL),
('475cb2ca-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8bfd-4bf0-11eb-8142-98fa9b10d0b1', 'v.insurance.nt@vinmec.com', 'dd82e756aa4cb93d61b947be2b90fa6f2fba91a27bcde7d65bb1e3b89fc5ebbe', 'Vinmec Nha Trang Hospital', 'v.insurance.nt@vinmec.com', 'No', 0, 0, '2019-01-02 07:21:44', NULL, NULL),
('475cba19-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8ce7-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@familyhospital.vn', 'b85c249f96150b0015f82eca53415d3852cd5305e1afca9dabfe368a7fa65405', 'Family hospital (Da Nang)', 'insurance@familyhospital.vn', 'No', 0, 0, '2019-01-02 07:24:00', NULL, NULL),
('475cc1ce-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8ddf-4bf0-11eb-8142-98fa9b10d0b1', 'v.insurance.dn@vinmec.com', '87713863d7a6f21a704971a812daed50d6f275035588ac562c3fafb5af3303e7', 'Vinmec Da Nang Hospital', 'v.insurance.dn@vinmec.com', 'No', 1, 1, '2020-02-12 14:57:16', NULL, NULL),
('475cc92d-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8ecb-4bf0-11eb-8142-98fa9b10d0b1', 'insurance.hp@vinmec.com', '6885b45e962d13f291f61fb68417f2970653a0497e7fc2166aeb7c0db105e5af', 'Vinmec Hai Phong Hospital', 'insurance.hp@vinmec.com', 'No', 0, 0, '2019-01-02 07:27:03', NULL, NULL),
('475ccff8-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d8fb0-4bf0-11eb-8142-98fa9b10d0b1', 'hmvp1.insurance@hoanmy.com ', 'aacd2de7d402d77ab2369c01951379111358e952fc2dfce7dc3cf3041811d281', 'Van Phuc 1 Hospital', 'hmvp1.insurance@hoanmy.com', 'No', 0, 0, '2019-01-02 07:31:07', NULL, NULL),
('475cd685-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9098-4bf0-11eb-8142-98fa9b10d0b1', 'phongkhth.dhy@gmail.com', '43d0246bf910037cf1f70ed341898e73671763a13e22f9e5a5b89cde70bb32ca', 'Hai Phong Medical University Hospital', 'phongkhth.dhy@gmail.com', 'No', 0, 0, '2019-01-02 07:32:56', NULL, NULL),
('475cdcf3-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9188-4bf0-11eb-8142-98fa9b10d0b1', 'info@benhvienbinhdinh.com.vn', 'c704bbdc87ad815706372dbcc12b1595e2ab6717adc9ff97d835048899215258', 'Binh Dinh Hospital', 'info@benhvienbinhdinh.com.vn', 'No', 0, 0, '2019-01-02 07:35:03', NULL, NULL),
('475ce387-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d92d5-4bf0-11eb-8142-98fa9b10d0b1', 'benhviendanang@danang.gov.vn', 'bce80547cd54d447947edefffeede304d57af3b5abb3104620bf11635440b782', 'Da Nang Hospital', 'benhviendanang@danang.gov.vn', 'No', 0, 0, '2019-01-02 07:36:36', NULL, NULL),
('475ceb50-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d93c8-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@aih.com.vn', 'd2f55d226217daf9b9817ea1ed576165f6a49d85133ce395341a8f3d98003dbe', 'American International Hospital', 'insurance@aih.com.vn', 'No', 0, 0, '2021-06-15 01:12:35', NULL, NULL),
('475cf2ee-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d94ba-4bf0-11eb-8142-98fa9b10d0b1', 'danang@vietnammedicalpractice.com', 'c7a2eea446365f16ece0ceee05fe58be75662807743defdd71ccb2cbb8b00ecb', 'Vietnam Medical Practice Da Nang', 'danang@vietnammedicalpractice.com', 'No', 0, 0, '2019-07-03 10:28:47', NULL, NULL),
('475d245f-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d959a-4bf0-11eb-8142-98fa9b10d0b1', 'insurancecare@victoriavn.com', 'b45af8614d11ebb4d1dbc16627833a264605d17dbdd77626886405e48f9b03da', 'Victoria My My Clinic', 'insurancecare@victoriavn.com', 'No', 0, 0, '2019-07-03 10:03:56', NULL, NULL),
('475d2c3b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d974f-4bf0-11eb-8142-98fa9b10d0b1', 'info@careplusvn.com', 'adb816afe92d1c98ce7030b5bbeb1d38eb2d115997aa77595db3e736acbf3213', 'Careplus Clinic', 'info@careplusvn.com', 'No', 0, 0, '2020-11-12 13:35:35', NULL, NULL),
('475d343a-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9832-4bf0-11eb-8142-98fa9b10d0b1', 'info@phuongchau.com', '80b0ed104df57dc7ad802e78e80f19a9f74da45832f110c5283b66e5d18e93ea', 'Phuong Chau International Hospital', 'info@phuongchau.com', 'No', 0, 0, '2019-01-02 10:32:43', NULL, NULL),
('475d3b71-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d990e-4bf0-11eb-8142-98fa9b10d0b1', 'minhanhhospital@gmail.com', '9b790783b9c679bf7ed7d0a630b4cffb507b0d32338dbc5725383927a0ff814d', 'Minh Anh Hospital', 'minhanhhospital@gmail.com', 'No', 0, 0, '2019-01-02 10:33:48', NULL, NULL),
('475d4283-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d99e7-4bf0-11eb-8142-98fa9b10d0b1', 'contactus.cuulong@hoanmy.com', '2f11a26057964bc7fa2f8bf85bf798ed750e7fc4d3e6eb179fbe2bf42ebbf495', 'Hoan My Cuu Long Hospital', 'contactus.cuulong@hoanmy.com', 'No', 0, 0, '2019-01-02 10:35:56', NULL, NULL),
('475d4993-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9aca-4bf0-11eb-8142-98fa9b10d0b1', 'contactus.dalat@hoanmy.com', '6e6c4893b2bcf0e6649aa5b0f5e3882f1a005f7bd0f8f75f914ccf982bb56ba6', 'Hoan My Da Lat Hospital', 'contactus.dalat@hoanmy.com', 'No', 0, 0, '2019-01-02 10:37:02', NULL, NULL),
('475d508e-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9ba2-4bf0-11eb-8142-98fa9b10d0b1', 'contactus.minhhai@hoanmy.com', '596279e458d725ae1cba61012d15e718dab5a0951c07c37dd59a736def7afc5e', 'Hoan My Minh Hai Hospital', 'contactus.minhhai@hoanmy.com', 'No', 0, 0, '2019-01-02 10:39:12', NULL, NULL),
('475d5817-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9c83-4bf0-11eb-8142-98fa9b10d0b1', 'info@vkhospital.com.vn', 'a5a9c2d4e7c4c21884c3473b3af89d9ef09fd77494e5ccdd07ebec76aa0f7d22', '22-12 Hospital (VK Hospital)', 'info@vkhospital.com.vn', 'No', 0, 0, '2019-01-02 10:41:17', NULL, NULL),
('475d5f80-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9d60-4bf0-11eb-8142-98fa9b10d0b1', 'cs@bvquoctevinh.com', '9186ac1f8708f2c75c217a8318c83959f01737bf97b2fc393360c67d55976165', 'Vinh International Hospital', 'cs@bvquoctevinh.com', 'No', 0, 0, '2019-01-02 10:44:17', NULL, NULL),
('475d664a-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9e44-4bf0-11eb-8142-98fa9b10d0b1', 'bhbl1@hongduchospital.vn', '9442b69d68a717998bad23607d4943693603bc709d552849b02964f01a8d0705', 'Hong Duc III Hospital', 'bhbl1@hongduchospital.vn', 'No', 0, 0, '2019-07-09 03:55:19', NULL, NULL),
('475d6d26-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9f1f-4bf0-11eb-8142-98fa9b10d0b1', 'benhviennhattan@gmail.com', '3ed04699d01c57c2824968d323dae06deaa8b34fff5ed15413f57a92cebd57e3', 'Nhat Tan Hospital', 'benhviennhattan@gmail.com', 'No', 0, 0, '2019-01-02 10:47:55', NULL, NULL),
('475d73e7-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9ffc-4bf0-11eb-8142-98fa9b10d0b1', 'sgbg.acc@gmail.com', '5be459c93bd092945b4f642e3e34c7625c38034e75f0cc5182da267db45ef4fd', 'Sai Gon Binh Duong Hospital', 'sgbg.acc@gmail.com', 'No', 0, 0, '2019-07-02 12:10:22', NULL, NULL),
('475d7adf-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da0db-4bf0-11eb-8142-98fa9b10d0b1', 'dongdohospital@gmail.com', 'fa1d002e888d0de92d529a177ad21618823d712ff9a9d4d4b930e4664ff3c20d', 'Dong Do Hospital', 'dongdohospital@gmail.com', 'No', 0, 0, '2019-01-02 10:53:24', NULL, NULL),
('475d864f-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da29a-4bf0-11eb-8142-98fa9b10d0b1', 'info@cthospital.vn', '4fa044e0d27d290fbfc53731fef69a24357713f08ab21cab649737c0f1dcb9c6', 'Cao Thang International Eye Hospital', 'info@cthospital.vn', 'No', 0, 0, '2019-01-02 11:30:21', NULL, NULL),
('475d8da4-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da37e-4bf0-11eb-8142-98fa9b10d0b1', 'info@ykhoadiamond.com', '4455c03c40e7e1e9dbdcfafbfabf9d1007f23e0870b4c326061fc98cb418c4c5', 'Diamond Clinic', 'info@ykhoadiamond.com', 'No', 0, 0, '2019-01-02 11:35:07', NULL, NULL),
('475d952a-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da464-4bf0-11eb-8142-98fa9b10d0b1', 'info@cmi-vietnam.com', '42e2f78cf3648e017010770e985c139c46ca39225adc28a4c1876bb03e67c7f0', 'Centre Médical International ', 'info@cmi-vietnam.com', 'No', 0, 0, '2019-07-03 11:09:16', NULL, NULL),
('475d9c3d-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da53c-4bf0-11eb-8142-98fa9b10d0b1', 'benhvienhungvuong@gmail.com', '423de0ed9b72274f5fca13913a82f4b14dba9d87cdfbccb14b96cbfa42cdde91', 'Hung Vuong Hospital', 'benhvienhungvuong@gmail.com', 'No', 0, 0, '2019-01-02 11:39:50', NULL, NULL),
('475da447-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da61c-4bf0-11eb-8142-98fa9b10d0b1', 'vmhl-cskh@vingroup.net', 'c1d50ce477dfe7a678c98ea9131e0315e4d969fa1d031e8034a4d7cd7b503bb6', 'Vinmec Ha Long Hospital', 'vmhl-cskh@vingroup.net', 'No', 0, 0, '2019-01-02 11:44:03', NULL, NULL),
('475dacb0-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da701-4bf0-11eb-8142-98fa9b10d0b1', 'info@maplehealthcare.net', '417ddcb8c732800e01fd8c12fb7d0e976b021cc3321e7d8bf953457f5782097c', 'Maple Healthcare', 'info@maplehealthcare.net', 'No', 0, 0, '2019-01-02 11:46:45', NULL, NULL),
('475db4b7-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da7eb-4bf0-11eb-8142-98fa9b10d0b1', 'info@medelab.vn', 'f279ae626467002e6b0abf99fc0511dd02fb73f1297d8831ae1ea62117486f9f', 'Medelab Clinic', 'info@medelab.vn', 'No', 0, 0, '2019-01-02 11:49:16', NULL, NULL),
('475dbcfa-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da8cb-4bf0-11eb-8142-98fa9b10d0b1', 'mcidasom@gmail.com', 'd40e602afd21e677de9c895212f4e923aae293aea843d4bb18d1621fd089fff9', 'Medical Care International ', 'mcidasom@gmail.com', 'No', 0, 0, '2019-07-03 04:24:39', NULL, NULL),
('475dc46e-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da9b0-4bf0-11eb-8142-98fa9b10d0b1', 'phongkhamphuckhang@gmail.com', 'd23051ed9fa9b5f5c687bb385dfd694f119a11627a19931573db65fe38208b45', 'Phuc Khang Clinic ', 'phongkhamphuckhang@gmail.com', 'No', 0, 0, '2019-01-02 12:17:01', NULL, NULL),
('475dcb2d-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2daa90-4bf0-11eb-8142-98fa9b10d0b1', 'info@stamfordskin.com', '931d0f738a827321e7d95cdb2908da5be7d227af62e2c84bd7a1d233c564754e', 'Stamford Skin Center', 'info@stamfordskin.com', 'No', 0, 0, '2019-01-02 12:22:35', NULL, NULL),
('475dd283-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dab78-4bf0-11eb-8142-98fa9b10d0b1', 'dichvukhachhang@vietsingclinic.com.vn', '361e81ee3c3ff7a8d41c1dac089457cb5366624484ff1217536d8004001ac971', 'Vietsing International Clinic', 'dichvukhachhang@vietsingclinic.com.vn', 'No', 0, 0, '2019-01-02 12:23:49', NULL, NULL),
('475de327-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dac5d-4bf0-11eb-8142-98fa9b10d0b1', 'info@benhvienbacha.vn', '12e8567514b9cf815a82a1011216fa46d02ce85f4ebdc76db0a2871369e89bc4', 'Bac Ha International Hospital', 'info@benhvienbacha.vn', 'No', 0, 0, '2019-01-02 12:24:32', NULL, NULL),
('475dec3c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dad40-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@healthcare.com.vn', 'd8af4e098b711e713684b7b5a115fa646c96b7605d65e7d5cc4eecf790206888', 'Vigor Clinic', 'insurance@healthcare.com.vn', 'No', 0, 0, '2019-01-02 12:26:11', NULL, NULL),
('475e2242-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dae21-4bf0-11eb-8142-98fa9b10d0b1', 'info@vietgiaclinic.com', 'b52681d545c23b612f2cf3fbfddfac85977c5db72e0d793b40b63aa0493dbdb0', 'Viet Gia Clinic', 'info@vietgiaclinic.com', 'No', 0, 0, '2019-01-02 12:27:10', NULL, NULL),
('475e2a22-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2daf02-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@yersinclinic.com', '29e479c3797cf24c7846b400867522cce31e409fd47aed23db4efe7cb694b891', 'Yersin International Clinic ', 'insurance@yersinclinic.com', 'No', 0, 0, '2021-03-07 10:54:59', NULL, NULL),
('475e31f7-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dafe4-4bf0-11eb-8142-98fa9b10d0b1', 'emc@ghs.com.vn', '9e2c28bcf434b2c42aa973c5aa65dbc9c2380458a5a35a280e3f35e637278cf2', 'European Medical Center', 'emc@ghs.com.vn', 'No', 0, 0, '2019-01-02 12:29:31', NULL, NULL),
('475e39a6-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2db0c2-4bf0-11eb-8142-98fa9b10d0b1', 'chamsockhachhang.pk1@umc.edu.vn', '3d0c750d1922f964f193903ee52bc0f2480d778cd49661edb1724dc349f4f34f', 'Medical University Clinic', 'chamsockhachhang.pk1@umc.edu.vn', 'No', 0, 0, '2019-01-02 12:30:50', NULL, NULL),
('475e40dc-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2db9e0-4bf0-11eb-8142-98fa9b10d0b1', 'info@nhidongthanhpho.com', '0b845df7d6b6a872199ec9799f35257ab413775c1449f0e971f9e18f5593bdce', 'Children Clinic', 'info@nhidongthanhpho.com', 'No', 0, 0, '2019-01-02 12:31:17', NULL, NULL),
('475e47ed-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dbc71-4bf0-11eb-8142-98fa9b10d0b1', 'infod5@maplehealthcare.net', 'aa2646241a42b0b6ee5d1372b53808e3fa3b6f092f3564ab67f2fba03411fd0c', 'Maple Healthcare Center', 'infod5@maplehealthcare.net', 'No', 0, 0, '2019-01-02 12:32:29', NULL, NULL),
('475e4f02-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dbe7b-4bf0-11eb-8142-98fa9b10d0b1', 'info@vungtauclinic.com', '405e99019c45d1fa3fdb3918cd96973ec1c11a37da82a0d326e07e22d4a251ec', 'Vung Tau Clinic', 'info@vungtauclinic.com', 'No', 0, 0, '2019-01-02 12:36:16', NULL, NULL),
('475e5637-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dc1a2-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@monacohealthcare.com', '301f7a53d34328bfafdb02356a6991d4ad4fc9303d26f1fdf4fbe764d0e37c2d', 'Monaco Healthcare', 'insurance@monacohealthcare.com', 'No', 1, 1, '2019-01-02 12:38:53', NULL, NULL),
('475e5d22-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dc324-4bf0-11eb-8142-98fa9b10d0b1', 'info@pkvietmy.com.vn', '25d8a68c5bd457e51c3d3a0ddc9e9309fe29b08170beb41213c402f43fd0ecd5', 'Viet My Clinic', 'info@pkvietmy.com.vn', 'No', 0, 0, '2019-01-02 12:40:58', NULL, NULL),
('475e637f-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da701-4bf0-11eb-8142-98fa9b10d0b1', 'infod3@maplehealthcare.net', 'dfadba600d5c681bdb192b57b4459a3acc27209ea1be9fcecafd34d5aff104d5', 'Maple Healthcare Branch 1', 'infod3@maplehealthcare.net', 'No', 0, 0, '2019-01-02 12:54:11', NULL, NULL),
('475e6983-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dc751-4bf0-11eb-8142-98fa9b10d0b1', 'info@pacifichealthcare.vn', '19f9c62e5289343beab775af5bd578547702b9c1cbc593bd3e7d2fdd2b5105da', 'Pacific Clinic', 'info@pacifichealthcare.vn', 'No', 0, 0, '2019-01-02 12:55:35', NULL, NULL),
('475e7021-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dc853-4bf0-11eb-8142-98fa9b10d0b1', 'hue_interhosp@hueimc.vn', '6bd1902eb4c9231ce643c61413729a1c298d82db8866ed0f08cb6d42ad60959e', 'Hue International Hospital', 'hue_interhosp@hueimc.vn', 'No', 0, 0, '2019-01-03 05:32:59', NULL, NULL),
('475e7615-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dc940-4bf0-11eb-8142-98fa9b10d0b1', 'maipts@benhvienanviet.com', '5e30549b3655a06cd8632475e1631171103d15302fab02c28fbb739bd9ccb794', 'An Viet Hospital', 'maipts@benhvienanviet.com', 'No', 0, 0, '2019-01-03 06:39:16', NULL, NULL),
('475e7c34-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dca29-4bf0-11eb-8142-98fa9b10d0b1', 'huyendm@benhvienhathanh.vn', 'cfb98f5f04dd5941166732652acd89cbc4e41f9083a1b2a1ea3461676d402dfe', 'Ha Thanh Hospital', 'huyendm@benhvienhathanh.vn', 'No', 0, 0, '2019-01-03 07:00:08', NULL, NULL),
('475e8279-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d79c7-4bf0-11eb-8142-98fa9b10d0b1', 'care1_reception@vietnammedicalpractice.com', '8c9f5a1b0461413b79ed27e7295967f6a00f206ecaf519972e8699ab86c23d87', 'Vietnam Medical Practice HCMC - Dist 1', 'care1_reception@vietnammedicalpractice.com', 'No', 0, 0, '2019-01-03 07:13:39', NULL, NULL),
('475e8898-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d79c7-4bf0-11eb-8142-98fa9b10d0b1', 'd2.reception@vietnammedicalpractice.com', 'ad67f8fd4537610e1b941ce78e08b18bfc1c4b86c0614a6ff880429149449e86', 'Vietnam Medical Practice HCMC - Dist 2', 'd2.reception@vietnammedicalpractice.com', 'No', 0, 0, '2019-01-03 07:14:43', NULL, NULL),
('475e9013-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da1ba-4bf0-11eb-8142-98fa9b10d0b1', 'baohiem@medlatec.vn', '2273b0cc007769235ed2c78c0f683d7b006c0fc4dc52a8b9cc69adc54a643202', 'Medlatec Hospital - Insurance', 'baohiem@medlatec.vn', 'No', 0, 0, '2020-02-23 14:07:57', NULL, NULL),
('475e9631-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dcb05-4bf0-11eb-8142-98fa9b10d0b1', 'dothao167@gmail.com', 'd8155c973b09c18d406c3289d5bf7ee1360036a9d6481d4e701d976fab109752', 'Phuong Dong International Hospital', 'dothao167@gmail.com', 'No', 0, 0, '2019-01-06 06:03:35', NULL, NULL),
('475e9c52-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dcbdf-4bf0-11eb-8142-98fa9b10d0b1', 'tp.taichinhketoan@tthgroupvinh.vn', 'e51d8593156608fa492501d6d67f6ac2f9c3002c63a57885be04f8afc10efa86', 'Nguyen Minh Hong Hospital', 'tp.taichinhketoan@tthgroupvinh.vn', 'No', 0, 0, '2019-01-06 09:40:41', NULL, NULL),
('475ea14c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dccbe-4bf0-11eb-8142-98fa9b10d0b1', 'nguyentanbvhp@gmail.com', '3a767f8a43edb55d0ab319c94321135cffba5e8ae0318fe63b9b87f846e69094', 'Hong Phuc Hospital', 'nguyentanbvhp@gmail.com', 'No', 0, 0, '2019-01-06 09:41:48', NULL, NULL),
('475ea5a8-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dcda1-4bf0-11eb-8142-98fa9b10d0b1', 'duyendt@benhvienhanoi.vn', 'ae16d47e0b08387d493bd61e8192a87869e9750739859a17f8a29e8994ac9aed', 'Ha Noi Hospital', 'duyendt@benhvienhanoi.vn', 'No', 0, 0, '2019-01-06 09:45:20', NULL, NULL),
('475ea9d7-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dce7b-4bf0-11eb-8142-98fa9b10d0b1', 'maiptt@tamanhhospital.vn', '6091a004747fc299beddaa4eb7b70a6a43a7cf37a20d0aa90af47db23ffe3773', 'Tam Anh Hospital', 'maiptt@tamanhhospital.vn', 'No', 0, 0, '2019-07-06 09:54:45', NULL, NULL),
('475eae27-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dcf57-4bf0-11eb-8142-98fa9b10d0b1', 'phongchamsockhachhang@bvquoctedongnai.com', '285ec8dedc40fe14c80ba32de12a92ada59591cf2d79150d73b30cd762418e9e', 'Dong Nai International Hospital', 'phongchamsockhachhang@bvquoctedongnai.com', 'No', 0, 0, '2019-01-06 09:52:11', NULL, NULL),
('475eb27e-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd031-4bf0-11eb-8142-98fa9b10d0b1', 'thaontp246@gmail.com', 'ca98048a9fca6a02e51d36784660e07debb6c44e683d3207a24dcad8c0d7b1dd', 'Hai Phong International Hospital', 'thaontp246@gmail.com', 'No', 0, 0, '2019-01-06 09:53:21', NULL, NULL),
('475eb6ae-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd104-4bf0-11eb-8142-98fa9b10d0b1', 'info@pkdkngocminh.com.vn', '55d24a61de7013daed1cefd5fbadee4fbf5e4e8dc23cd9c5ec237227702175eb', 'Ngoc Minh Clinic', 'info@pkdkngocminh.com.vn', 'No', 0, 0, '2019-01-06 09:56:15', NULL, NULL),
('475ebb20-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd1fe-4bf0-11eb-8142-98fa9b10d0b1', 'insurance.saigon@columbiaasia.com', '98a2bf4e671e1cce52b5a732da2ab05d58e60a9c9081d493be44dede63290330', 'Columbia Asia Sai Gon Clinic', 'insurance.saigon@columbiaasia.com', 'No', 0, 0, '2019-07-09 09:25:41', NULL, NULL),
('475ebf2b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d66a6-4bf0-11eb-8142-98fa9b10d0b1', 'quaybh@hongngochospital.vn', 'eb41a222472a869e245088bd020fb55e95129238ada4588c34d7dc8a6266d197', 'Hong Ngoc Hospital', 'quaybh@hongngochospital.vn', 'No', 0, 0, '2019-01-14 11:54:07', NULL, NULL),
('475ec2e2-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thamtong', '31d3c306addb171281e8addb195f4497098dcc1dc1555812378dcba383a53cb7', 'Tống Thị Thắm', 'thamtong@pacificcross.com.vn', 'No', 1, 1, '2019-02-13 09:20:37', NULL, NULL),
('475ec6b1-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'yenha', '6559c650756c8c8f87bf606b38440f340a8ee524ce198011e458ac2c0c355b86', 'Hà Thị Hải Yến', 'yenha@pacificcross.com.vn', 'No', 1, 1, '2019-02-13 09:21:53', NULL, NULL),
('475eca8a-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd2dc-4bf0-11eb-8142-98fa9b10d0b1', 'info@giaan115.com', '63ae1d294ecb226890578f660dbae9832a38d50082597d1d105a217601989fbc', 'Gia An 115 Hospital', 'info@giaan115.com', 'No', 1, 1, '2019-02-25 06:07:22', NULL, NULL),
('475ece52-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e6887d6-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d93c8-4bf0-11eb-8142-98fa9b10d0b1', 'xavo', 'fd99ff45f50ec2e603adb497faf46941a130d21528a162352cff13761852893c', 'Vo Quan Xa', 'xav@pacificcross.com.vn', 'No', 1, 1, '2019-03-07 02:15:30', NULL, NULL),
('475ed24e-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d9098-4bf0-11eb-8142-98fa9b10d0b1', 'hanhnam112010@gmail.com', '759fb7d6d0ea9eec4f18085e1d2b5c49e4750bc24ed0702e3f5bc4eacfce9972', 'Hanh Nam 11-2010', 'hanhnam112010@gmail.com', 'No', 1, 1, '2019-10-07 12:15:27', NULL, NULL),
('475ed63d-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd3be-4bf0-11eb-8142-98fa9b10d0b1', 'insurance@nih.com.vn', '160375b37905c1532c5e71baeca05d642f8d1f567c7b06ecad563d6af4951105', 'Nam Sai Gon International Hospital', 'insurance@nih.com.vn', 'No', 1, 1, '2020-03-19 18:39:01', NULL, NULL),
('475eda4e-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd811-4bf0-11eb-8142-98fa9b10d0b1', 'nguyenngoc7576@yahoo.com.vn', '94e09695074e91c9a73fae773e6ea98ae2807aced7bf7e6a3cf3f7f598da9661', 'Binh Dan General Hospital', 'nguyenngoc7576@yahoo.com.vn', 'No', 1, 1, '2019-09-19 20:02:40', NULL, NULL),
('475ede66-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd496-4bf0-11eb-8142-98fa9b10d0b1', 'trietvm@medicbinhduong.vn', 'cd8e96c36c1424a6bfea142edc943a2b8543d341136a2f99e8cc99a0646cb4cc', 'Medic-BD General Hospital', 'trietvm@medicbinhduong.vn', 'No', 1, 1, '2019-09-19 20:05:14', NULL, NULL),
('475ee2dc-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd575-4bf0-11eb-8142-98fa9b10d0b1', 'thuypth@dolifehospital.vn', '2b66d91e218cf3cf2ecfa4283249c4518731c2e9c8e9f168545e69c935449a83', 'dolife International Hospital', 'thuypth@dolifehospital.vn', 'No', 1, 1, '2019-09-19 20:15:28', NULL, NULL),
('475ee7a6-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd811-4bf0-11eb-8142-98fa9b10d0b1', '', '94e09695074e91c9a73fae773e6ea98ae2807aced7bf7e6a3cf3f7f598da9661', 'Binh Dan General Hospital', '', 'No', 1, 1, '2019-09-19 20:20:23', NULL, NULL),
('475eeb6b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thanhle', '2bd6aa2432e104bd5a19cf054952ad65267bd2bc9724652fe53ca7ec467ba52d', 'Lê Hoàng Ngọc Thanh', 'thanhle@pacificcross.com.vn', 'No', 1, 1, '2020-06-18 14:46:10', NULL, NULL),
('475eef91-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2da464-4bf0-11eb-8142-98fa9b10d0b1', 'mai.nguyen@cmi-vietnam.com', '4de6b8bb1616b590ae297887815420607e98b8fbdfbffd59de2abe634d20696c', 'Mai Nguyen', 'mai.nguyen@cmi-vietnam.com', 'No', 1, 1, '2021-01-15 15:56:03', NULL, NULL),
('475ef397-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dfb18-4bf0-11eb-8142-98fa9b10d0b1', 'clinic@beekidsclinic.com', '9fecc44735861abb66c5d9518bbe53746462f15df2a6180e6f20edb2b0bc27e4', 'Bee Kids Clinic', 'clinic@beekidsclinic.com', 'No', 1, 1, '2020-07-17 12:15:06', NULL, NULL),
('475ef76f-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dc599-4bf0-11eb-8142-98fa9b10d0b1', 'dothihien@medlatec.vn', '86bb0a2fc7f5227f538bb7e494b436b96e1c8398b998b9b03a3cf8864a5b2dc6', 'Medlatec Thanh Xuan', 'dothihien@medlatec.vn', 'No', 1, 1, '2020-08-07 10:55:36', NULL, NULL),
('475efb5c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dc599-4bf0-11eb-8142-98fa9b10d0b1', 'vunhatlam@medlatec.vn', '6b72baa02246d23685acbc670a6426f18b52b1211d1cf64162cd3a0f455a7d16', 'Medlatec Tay Ho', 'vunhatlam@medlatec.vn', 'No', 1, 1, '2020-01-14 13:23:42', NULL, NULL),
('475eff76-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dfc0c-4bf0-11eb-8142-98fa9b10d0b1', 'baohiem.tdh@phongkhamthucuc.vn', 'de2f46a0160f4fcd7f63c91b68d4e4f07524d21f94cb0f3229052c9e0a1d83c2', 'Thu Cuc Clinic', 'baohiem.tdh@phongkhamthucuc.vn', 'No', 1, 1, '2020-07-14 13:39:56', NULL, NULL),
('475f0387-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2de291-4bf0-11eb-8142-98fa9b10d0b1', 'baohiemdnd@gmail.com', 'f8854b5040170e7ad116068348aa77c2c3657df9f7f1a44826ab4467b992190a', 'DND International Eye Hospital', 'baohiemdnd@gmail.com', 'No', 1, 1, '2020-01-14 13:41:04', NULL, NULL),
('475f0744-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2de1a9-4bf0-11eb-8142-98fa9b10d0b1', 'baolanhvienphi.bvvinhduc@gmail.com', '7c8accd623608d68b02d311bcce6ef7fa93eb3e394ebddf13278dfa3ef447d4c', 'Vinh Duc Hospital', 'baolanhvienphi.bvvinhduc@gmail.com', 'No', 1, 1, '2020-01-14 13:51:04', NULL, NULL),
('475f0b50-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2de093-4bf0-11eb-8142-98fa9b10d0b1', 'baolanh.lacviet@gmail.com', '5aa70907043a12871637b068dd1394dfcb0979744888e1626610b82b790c59e8', 'LacViet Friendship Hospital', 'baolanh.lacviet@gmail.com', 'No', 1, 1, '2020-01-14 13:54:47', NULL, NULL),
('475f0f0f-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dde37-4bf0-11eb-8142-98fa9b10d0b1', 'chamsockhachhang@hoabinhhospital.com.vn', 'af3f4ecb69fd45712c5642504cd115b95ddef83f35c93587b30993d0b109a652', 'Hoa Binh Hospital - Customer Care', 'chamsockhachhang@hoabinhhospital.com.vn', 'No', 1, 1, '2020-07-14 17:39:06', NULL, NULL),
('475f1334-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dde37-4bf0-11eb-8142-98fa9b10d0b1', 'kinhdoanh-ksk@hoabinhhospital.com.vn', 'af3f4ecb69fd45712c5642504cd115b95ddef83f35c93587b30993d0b109a652', 'Hoa Binh Hospital - Business', 'kinhdoanh-ksk@hoabinhhospital.com.vn', 'No', 1, 1, '2020-07-14 17:41:04', NULL, NULL),
('475f16f1-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2ddb9a-4bf0-11eb-8142-98fa9b10d0b1', 'Thoa.dk@thiennhanhospital.com', '3e9ecfe13727b4fc71d9dbfcc8675cf66d0073cfd1392d3ff4b71dcdafc3cfb4', 'Thien Nhan Hospital - Ms. Thoa', 'Thoa.dk@thiennhanhospital.com', 'No', 1, 1, '2020-01-14 14:39:56', NULL, NULL),
('475f1aad-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2dd72b-4bf0-11eb-8142-98fa9b10d0b1', 'tuongvy021095@gmail.com', 'a65d6edd93204cd0d7ae2d4a9b18e6968259d26f8b0f2a4112e1269da54900a3', 'Ms. Vy - Hoan My ITO Dong Nai', 'tuongvy021095@gmail.com', 'No', 1, 1, '2020-10-22 16:19:24', NULL, NULL),
('475f1e56-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'ngocbaonguyen', 'e46e0ba5cdcde4ab32d8dcf15d75eee2ae8207962d713ba8a6a213c86293d8d9', 'Nguyễn Đình Bảo Ngọc', 'ngocbaonguyen@pacificcross.com.vn', 'No', 1, 1, '2021-04-06 17:08:07', NULL, NULL),
('475f24a9-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e0329-4bf0-11eb-8142-98fa9b10d0b1', 'an.leluong@hfh.com.vn', '17e1c609a51341982548278c1e5271bf0ef4559d58a19bcdc8730c9bb13a9908', 'Le Thi Luong An', 'an.leluong@hfh.com.vn', 'No', 1, 1, '2020-10-23 20:01:19', NULL, NULL),
('475f2927-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d83e5-4bf0-11eb-8142-98fa9b10d0b1', 'Baohiem@benhvienhungviet.vn', '65bb9b81305b2a1e184d7f2543ebe626c490e1af3fc03fe533ea2a9565a969fc', 'Hung Viet Hospital', 'Baohiem@benhvienhungviet.vn', 'No', 1, 1, '2020-11-07 10:37:16', NULL, NULL),
('475f2d5e-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2ddd53-4bf0-11eb-8142-98fa9b10d0b1', 'benhvien@saigonito.com', '29db0e89c64bd7b4e65dbf06c56fb8c2da6d8ebbb4aa84644377902ee1dd8224', 'Sai Gon-ITO Hospital', 'benhvien@saigonito.com', 'No', 1, 1, '2020-11-11 13:44:59', NULL, NULL),
('475f642b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thaoluu', 'a38c071315c96eb98353a03f168ae83205967a51809dbb4e6d8dfa3dbe789ee1', 'Lưu Ngọc Như Thảo', 'thaoluu@pacificcross.com.vn', 'No', 1, 1, '2020-11-25 19:31:25', NULL, NULL),
('475f6f0d-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e05cf-4bf0-11eb-8142-98fa9b10d0b1', 'thao.truong@hoanmy.com', '9a7879b309998b6df3ca293c76acf60469ef483c4d0c96395981c9203d786a73', 'Thao Truong - Hoan My Binh Phuoc Hospital', 'thao.truong@hoanmy.com', 'No', 1, 1, '2020-11-28 10:48:38', NULL, NULL),
('475f795c-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'cskh02@ykhoadiamond.com', 'fa54ab0aedd474e298c3309e1c4dd638741fb8805ce02457ad827ae50e3bea90', 'Phòng khám Diamond (Võ Thị Sáu)', 'cskh02@ykhoadiamond.com', 'No', 1, 1, '2020-12-08 17:14:34', NULL, NULL),
('475f88cd-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2d99e7-4bf0-11eb-8142-98fa9b10d0b1', 'blvp.hoanmycuulong@gmail.com', '9144fde0c3700ddb7e9261f8482c1c00a07af0862b9ddbe6a31a69b488394100', 'Hoan My Cuu Long Hospital', 'blvp.hoanmycuulong@gmail.com', 'No', 1, 1, '2020-06-02 19:52:02', NULL, NULL);
INSERT INTO `user` (`id`, `crt_by`, `crt_at`, `upd_by`, `upd_at`, `lzarole_id`, `provider_id`, `username`, `password`, `fullname`, `email`, `is_admin`, `notify`, `enabled`, `expiry`, `last_reset_by`, `last_reset_at`) VALUES
('475f92ce-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e0796-4bf0-11eb-8142-98fa9b10d0b1', 'nguyenhuudung007@gmail.com', '3690e6f270eb6badd210c2d569a080a0f1c354360c3162517d1a6865182eaca1', 'Le Loi Hospital', 'nguyenhuudung007@gmail.com', 'No', 1, 1, '2020-06-03 19:31:25', NULL, NULL),
('475f9ca4-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e08dd-4bf0-11eb-8142-98fa9b10d0b1', 'info@phongkhamthaianh.com', '42210b4c5a5edf11fe6ec8d1287132b4667c25cf33d13774397309c24d173bac', 'Thai Anh General clinic', 'info@phongkhamthaianh.com', 'No', 1, 1, '2020-12-14 14:24:06', NULL, NULL),
('475fa6e8-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e09c8-4bf0-11eb-8142-98fa9b10d0b1', 'bhytthienduc@gmail.com', 'eca5b5ac55965646071f57e3eee6889829aa05505cb6e11d9f269e92f573cbe2', 'Thien Duc General International Hospital', 'bhytthienduc@gmail.com', 'No', 1, 1, '2020-12-15 11:29:27', NULL, NULL),
('475fb055-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'hahuynh', '1dc86dfb587147ec0f822aa21926296986fc46467dc3b7cd0cebe1bedd54463f', 'Huỳnh Tô Há', 'hahuynh@pacificcross.com.vn', 'No', 1, 1, '2021-06-29 21:12:58', NULL, NULL),
('475fba17-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e688895-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'vantran', 'd21ee29c5bbf01aec608b7f8b0580b493bd96d8d899142cfafe89b868a67fcfb', 'Trần Khánh Vân', 'vantran@pacificcross.com.vn', 'No', 1, 1, '2021-05-08 12:38:04', NULL, NULL),
('475fc366-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'chinhle', '91f6331f63216bd0372e8abf803e0dcdec530fe931a4aa6c7a22622fc0e9e6c3', 'Lê Văn Chỉnh', 'chinhle@pacificcross.com.vn', 'No', 1, 1, '2020-12-28 18:41:55', NULL, NULL),
('475fcd29-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e06ad-4bf0-11eb-8142-98fa9b10d0b1', 'tieulam9999@gmail.com', '62e7026308a7ba7e07c450d313721819baf9bce98e7484dd7e36e91dbc3c05f8', 'Benh vien Da khoa Hoa Hao - Medic Can Tho', 'tieulam9999@gmail.com', 'No', 1, 1, '2020-06-28 12:59:34', NULL, NULL),
('475fd6f2-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'nhanle@pacificcross.com.vn', '7d57f933eaa4beb3a3af18beb05c10492477b3309b586a7a090324ef22391680', 'Le Van Nhan', 'nhanle@pacificcross.com.vn', 'No', 1, 1, '2020-06-28 14:32:22', NULL, NULL),
('475fe08b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e0ab0-4bf0-11eb-8142-98fa9b10d0b1', 'trang.ht@umc.edu.vn', '546f36371c1415fed70e2c999595d4d28c16340997efb87985da66012677245b', 'Ms. Trang - UMC', 'trang.ht@umc.edu.vn', 'No', 1, 1, '2020-06-29 17:14:44', NULL, NULL),
('475fea3b-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e0d50-4bf0-11eb-8142-98fa9b10d0b1', 'benhvienquocanhcare@gmail.com', '1b9d334e33006226d115227a956b91a5f67d48c0e22323b4a20667ba49cf3dd8', 'Quoc Anh General Hospital', 'benhvienquocanhcare@gmail.com', 'No', 1, 1, '2020-07-27 11:40:36', NULL, NULL),
('475ff40a-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e0f0c-4bf0-11eb-8142-98fa9b10d0b1', 'honghanhviethan@gmail.com', 'c65c546a5e9f98bd24a74c643b0de55ea20259c5eb8a42aff04b4f57a0755047', 'Viet Han General Clinic', 'honghanhviethan@gmail.com', 'No', 1, 1, '2020-07-27 11:49:14', NULL, NULL),
('475ffe1f-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e0e33-4bf0-11eb-8142-98fa9b10d0b1', 'linh.dtt@thanhchanclinic.vn', '4cdcd1cc85c91e64474966fb3f4078669e107efcd1bd41de63d5b19aa60c7d9c', 'Thanh Chan Clinic', 'linh.dtt@thanhchanclinic.vn', 'No', 1, 1, '2020-07-27 12:06:17', NULL, NULL),
('4760077f-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e0ff0-4bf0-11eb-8142-98fa9b10d0b1', 'khaibvth@gmail.com', 'a76f953d3ee26249868049dea4fd0ba6b8a36d85a0990afe3fdfca7e3d59c054', 'Thanh Ha Hospital', 'khaibvth@gmail.com', 'No', 1, 1, '2020-07-29 18:36:46', NULL, NULL),
('476010b6-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e1a03-4bf0-11eb-8142-98fa9b10d0b1', 'gretchen_kus@phyathai.com', 'eb1199a3251bc4a15c3abac0bdcb0d0c287d9dc37380e47d08a956963e745b22', 'Phyathai Nawamin Hospital', 'gretchen_kus@phyathai.com', 'No', 1, 1, '2021-02-20 20:03:57', NULL, NULL),
('476019a2-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e1d95-4bf0-11eb-8142-98fa9b10d0b1', 'dangoanh1408@gmail.com', '5031d719a3cbc80676728f044c9213d7002b92b217710ef2a17259a65d57b75f', 'Bệnh viện Quốc tế Hoàn Mỹ', 'dangoanh1408@gmail.com', 'No', 1, 1, '2020-08-30 18:49:41', NULL, NULL),
('476022b5-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2be414-4bf0-11eb-8142-98fa9b10d0b1', 'thanhdang1', '7f234b6579a0b93521aa0a7842ff88b3706c41cb14d123310bc0b22da3576a5f', 'Thanh1', 'thanhdang1@pacificcross.com.vn', 'No', 1, 1, '2020-10-20 11:40:28', NULL, NULL),
('47602c67-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e1f5f-4bf0-11eb-8142-98fa9b10d0b1', 'phuongtn215@gmail.com', 'e2a535aaa7fedd2e06ee9ededaebf67b3447db98809b7cc9d5b186c4091c88f2', 'Phòng Khám Sản Phụ Khoa Hiền Đức', 'phuongtn215@gmail.com', 'No', 1, 1, '2021-06-07 19:19:30', NULL, NULL),
('47603594-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e212d-4bf0-11eb-8142-98fa9b10d0b1', 'khamtyc@gmail.com', '19c16137c381db51a07d8fb67a7daf2e2da464434ae5d3d38e0f48d2061b85fe', 'Phong kham 182 Luong The Vinh', 'khamtyc@gmail.com', 'No', 1, 1, '2020-12-07 01:43:11', NULL, NULL),
('47603fe1-4bf3-11eb-8142-98fa9b10d0b1', NULL, '2021-01-01 05:36:21', NULL, NULL, '2e67102d-4a66-11eb-a7cf-98fa9b10d0b1', 'fd2e204b-4bf0-11eb-8142-98fa9b10d0b1', 'oanhpkvp@gmail.com', '57f6a08800d9b8fc69370f1d70869ee338cb329a1800356def6ea46e8c1c447b', 'Phong Kham Van Phuoc Cuu Long', 'oanhpkvp@gmail.com', 'No', 1, 1, '2020-12-13 20:12:52', NULL, NULL);

--
-- Triggers `user`
--
DROP TRIGGER IF EXISTS `user__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `user__id` BEFORE INSERT ON `user` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_module`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_module`;
CREATE TABLE `user_module` (
`username` varchar(50)
,`level` int(3) unsigned
,`id` char(50)
,`db_id` varchar(20)
,`name` char(50)
,`parent` varchar(50)
,`icon` varchar(50)
,`single` varchar(50)
,`plural` varchar(50)
,`single_vi` varchar(50)
,`plural_vi` varchar(50)
,`note` varchar(500)
,`display` varchar(100)
,`sort` varchar(100)
,`enabled` enum('Yes','No')
,`settings` varchar(500)
,`order_by` int(3) unsigned
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_permission`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_permission`;
CREATE TABLE `user_permission` (
`username` varchar(50)
,`module_id` char(50)
,`level` int(3) unsigned
);

-- --------------------------------------------------------

--
-- Table structure for table `user_reset_password`
--

DROP TABLE IF EXISTS `user_reset_password`;
CREATE TABLE `user_reset_password` (
  `id` char(36) NOT NULL,
  `crt_by` char(50) DEFAULT NULL,
  `crt_at` timestamp NULL DEFAULT current_timestamp(),
  `upd_by` char(50) DEFAULT NULL,
  `upd_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `email` varchar(200) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `user_reset_password`
--

TRUNCATE TABLE `user_reset_password`;
--
-- Triggers `user_reset_password`
--
DROP TRIGGER IF EXISTS `user_reset_password__id`;
DELIMITER $$
CREATE DEFINER=`card_validation`@`localhost` TRIGGER `user_reset_password__id` BEFORE INSERT ON `user_reset_password` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure for view `lzauser`
--
DROP TABLE IF EXISTS `lzauser`;

DROP VIEW IF EXISTS `lzauser`;
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`card_validation`@`localhost` SQL SECURITY DEFINER VIEW `lzauser`  AS SELECT `user`.`id` AS `id`, `user`.`crt_by` AS `crt_by`, `user`.`crt_at` AS `crt_at`, `user`.`upd_by` AS `upd_by`, `user`.`upd_at` AS `upd_at`, `user`.`lzarole_id` AS `lzarole_id`, `user`.`provider_id` AS `provider_id`, `user`.`username` AS `username`, `user`.`password` AS `password`, `user`.`fullname` AS `fullname`, `user`.`email` AS `email`, `user`.`is_admin` AS `is_admin`, `user`.`notify` AS `notify`, `user`.`enabled` AS `enabled`, `user`.`expiry` AS `expiry`, `user`.`last_reset_by` AS `last_reset_by`, `user`.`last_reset_at` AS `last_reset_at` FROM `user` ;

-- --------------------------------------------------------

--
-- Structure for view `user_module`
--
DROP TABLE IF EXISTS `user_module`;

DROP VIEW IF EXISTS `user_module`;
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`card_validation`@`localhost` SQL SECURITY DEFINER VIEW `user_module`  AS SELECT `p`.`username` AS `username`, `p`.`level` AS `level`, `m`.`id` AS `id`, `m`.`db_id` AS `db_id`, `m`.`id` AS `name`, `m`.`parent` AS `parent`, `m`.`icon` AS `icon`, `m`.`single` AS `single`, `m`.`plural` AS `plural`, `m`.`single_vi` AS `single_vi`, `m`.`plural_vi` AS `plural_vi`, `m`.`note` AS `note`, `m`.`display` AS `display`, `m`.`sort` AS `sort`, `m`.`enabled` AS `enabled`, `m`.`settings` AS `settings`, `m`.`order_by` AS `order_by` FROM (`user_permission` `p` join `lzamodule` `m` on(`p`.`module_id` = `m`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `user_permission`
--
DROP TABLE IF EXISTS `user_permission`;

DROP VIEW IF EXISTS `user_permission`;
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`card_validation`@`localhost` SQL SECURITY DEFINER VIEW `user_permission`  AS SELECT `u`.`username` AS `username`, `m`.`id` AS `module_id`, `p`.`level` AS `level` FROM (((`user` `u` join `lzarole` `r` on(`u`.`lzarole_id` = `r`.`id`)) join `lzapermission` `p` on(`p`.`lzarole_id` = `r`.`id`)) join `lzamodule` `m` on(`p`.`lzamodule_id` = `m`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cathay_benefit`
--
ALTER TABLE `cathay_benefit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cathay_benefit_cathay_head_fk` (`cathay_head_id`);

--
-- Indexes for table `cathay_claim_line`
--
ALTER TABLE `cathay_claim_line`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cathay_claim_line2`
--
ALTER TABLE `cathay_claim_line2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cathay_db_claim`
--
ALTER TABLE `cathay_db_claim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cathay_db_claim_cathay_history_fk` (`cathay_history_id`),
  ADD KEY `cathay_db_claim_cathay_head_fk` (`cathay_head_id`);

--
-- Indexes for table `cathay_db_claim_history`
--
ALTER TABLE `cathay_db_claim_history`
  ADD PRIMARY KEY (`id`,`valid_from`);

--
-- Indexes for table `cathay_head`
--
ALTER TABLE `cathay_head`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cathay_head_cathay_benefit_fk` (`cathay_benefit_id`);

--
-- Indexes for table `cathay_history`
--
ALTER TABLE `cathay_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cathay_history_provider_fk` (`provider_id`);

--
-- Indexes for table `cathay_history_history`
--
ALTER TABLE `cathay_history_history`
  ADD PRIMARY KEY (`id`,`valid_from`);

--
-- Indexes for table `cathay_member`
--
ALTER TABLE `cathay_member`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cathay_member2`
--
ALTER TABLE `cathay_member2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fubon_benefit`
--
ALTER TABLE `fubon_benefit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fubon_benefit_fubon_head_fk` (`fubon_head_id`);

--
-- Indexes for table `fubon_claim_line`
--
ALTER TABLE `fubon_claim_line`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fubon_claim_line2`
--
ALTER TABLE `fubon_claim_line2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fubon_client`
--
ALTER TABLE `fubon_client`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fubon_client2`
--
ALTER TABLE `fubon_client2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fubon_db_claim`
--
ALTER TABLE `fubon_db_claim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fubon_db_claim_fubon_history_fk` (`fubon_history_id`),
  ADD KEY `fubon_db_claim_fubon_head_fk` (`fubon_head_id`);

--
-- Indexes for table `fubon_db_claim_history`
--
ALTER TABLE `fubon_db_claim_history`
  ADD PRIMARY KEY (`id`,`valid_from`);

--
-- Indexes for table `fubon_head`
--
ALTER TABLE `fubon_head`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fubon_head_fubon_benefit_fk` (`fubon_benefit_id`);

--
-- Indexes for table `fubon_history`
--
ALTER TABLE `fubon_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fubon_history_provider_fk` (`provider_id`);

--
-- Indexes for table `fubon_history_history`
--
ALTER TABLE `fubon_history_history`
  ADD PRIMARY KEY (`id`,`valid_from`);

--
-- Indexes for table `fubon_member`
--
ALTER TABLE `fubon_member`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fubon_member2`
--
ALTER TABLE `fubon_member2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzaapi`
--
ALTER TABLE `lzaapi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lzaapi_username` (`username`);

--
-- Indexes for table `lzaemail`
--
ALTER TABLE `lzaemail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzafield`
--
ALTER TABLE `lzafield`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lzafield_lzamodule_fk` (`lzamodule_id`);

--
-- Indexes for table `lzafilter`
--
ALTER TABLE `lzafilter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lzafilter_name_module_user` (`name`,`user_id`,`lzamodule_id`),
  ADD KEY `lzafilter_lzamodule_fk` (`lzamodule_id`),
  ADD KEY `lzafilter_user_fk` (`user_id`),
  ADD KEY `lzafilter_lzafield_fk` (`lzafield_id`);

--
-- Indexes for table `lzahttprequest`
--
ALTER TABLE `lzahttprequest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzalanguage`
--
ALTER TABLE `lzalanguage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lzalanguage_name` (`name`);

--
-- Indexes for table `lzamodule`
--
ALTER TABLE `lzamodule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzanotification`
--
ALTER TABLE `lzanotification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzapermission`
--
ALTER TABLE `lzapermission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lzapermission_lzamodule_fk` (`lzamodule_id`),
  ADD KEY `lzapermission_lzarole_fk` (`lzarole_id`);

--
-- Indexes for table `lzarole`
--
ALTER TABLE `lzarole`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lzarole_name` (`name`),
  ADD UNIQUE KEY `lzarole_name_vi` (`name_vi`);

--
-- Indexes for table `lzasection`
--
ALTER TABLE `lzasection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzasession`
--
ALTER TABLE `lzasession`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzasetting`
--
ALTER TABLE `lzasetting`
  ADD UNIQUE KEY `lzasetting_key` (`id`),
  ADD KEY `lzasetting_lzasection_fk` (`lzasection_id`);

--
-- Indexes for table `lzasms`
--
ALTER TABLE `lzasms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lzastatistic`
--
ALTER TABLE `lzastatistic`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lzastatistic_name_module_user` (`name`,`user_id`,`lzamodule_id`),
  ADD KEY `lzastatistic_lzamodule_fk` (`lzamodule_id`),
  ADD KEY `lzastatistic_lzafield_fk` (`lzafield_id`),
  ADD KEY `lzastatistic_user_fk` (`user_id`);

--
-- Indexes for table `lzatask`
--
ALTER TABLE `lzatask`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lzatext`
--
ALTER TABLE `lzatext`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lzatext_name` (`name`);

--
-- Indexes for table `lzaview`
--
ALTER TABLE `lzaview`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lzaview_name` (`name`),
  ADD KEY `lzaview_lzamodule_fk` (`lzamodule_id`);

--
-- Indexes for table `mobile_claim`
--
ALTER TABLE `mobile_claim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mobile_claim_mobile_user_fk` (`mobile_user_id`),
  ADD KEY `mobile_claim_mobile_user_bank_account_fk` (`mobile_user_bank_account_id`),
  ADD KEY `mobile_claim_mobile_claim_status_fk` (`mobile_claim_status_id`);

--
-- Indexes for table `mobile_claim_file`
--
ALTER TABLE `mobile_claim_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mobile_claim_file_mobile_claim_fk` (`mobile_claim_id`);

--
-- Indexes for table `mobile_claim_otp`
--
ALTER TABLE `mobile_claim_otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_claim_status`
--
ALTER TABLE `mobile_claim_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_device`
--
ALTER TABLE `mobile_device`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mobile_device_mobile_user_fk` (`mobile_user_id`);

--
-- Indexes for table `mobile_user`
--
ALTER TABLE `mobile_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile_user_mbr_no` (`mbr_no`);

--
-- Indexes for table `mobile_user_bank_account`
--
ALTER TABLE `mobile_user_bank_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile_user_bank_account` (`bank_name`,`bank_acc_no`),
  ADD KEY `mobile_user_bank_account_mobile_user_fk` (`mobile_user_id`);

--
-- Indexes for table `mobile_user_reset_password`
--
ALTER TABLE `mobile_user_reset_password`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_user_session`
--
ALTER TABLE `mobile_user_session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pcv_benefit`
--
ALTER TABLE `pcv_benefit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pcv_benefit_pcv_head_fk` (`pcv_head_id`);

--
-- Indexes for table `pcv_benefit_provider`
--
ALTER TABLE `pcv_benefit_provider`
  ADD PRIMARY KEY (`provider_id`,`pcv_benefit_id`) USING BTREE,
  ADD KEY `pcv_benefit_id` (`pcv_benefit_id`);

--
-- Indexes for table `pcv_claim_line`
--
ALTER TABLE `pcv_claim_line`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pcv_claim_line2`
--
ALTER TABLE `pcv_claim_line2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pcv_db_claim`
--
ALTER TABLE `pcv_db_claim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pcv_db_claim_pcv_history_fk` (`pcv_history_id`),
  ADD KEY `pcv_db_claim_pcv_head_fk` (`pcv_head_id`);

--
-- Indexes for table `pcv_db_claim_history`
--
ALTER TABLE `pcv_db_claim_history`
  ADD PRIMARY KEY (`id`,`valid_from`);

--
-- Indexes for table `pcv_head`
--
ALTER TABLE `pcv_head`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pcv_head_pcv_benefit_fk` (`pcv_benefit_id`);

--
-- Indexes for table `pcv_history`
--
ALTER TABLE `pcv_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pcv_history_provider_fk` (`provider_id`);

--
-- Indexes for table `pcv_history_history`
--
ALTER TABLE `pcv_history_history`
  ADD PRIMARY KEY (`id`,`valid_from`);

--
-- Indexes for table `pcv_member`
--
ALTER TABLE `pcv_member`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pcv_member2`
--
ALTER TABLE `pcv_member2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pcv_plan_desc_map`
--
ALTER TABLE `pcv_plan_desc_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_slug` (`slug`);

--
-- Indexes for table `provider`
--
ALTER TABLE `provider`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_username` (`username`),
  ADD UNIQUE KEY `user_email` (`email`),
  ADD KEY `user_lzarole_fk` (`lzarole_id`),
  ADD KEY `user_provider_fk` (`provider_id`);

--
-- Indexes for table `user_reset_password`
--
ALTER TABLE `user_reset_password`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userrstpwd_email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cathay_benefit`
--
ALTER TABLE `cathay_benefit`
  ADD CONSTRAINT `cathay_benefit_cathay_head_fk` FOREIGN KEY (`cathay_head_id`) REFERENCES `cathay_head` (`id`);

--
-- Constraints for table `cathay_db_claim`
--
ALTER TABLE `cathay_db_claim`
  ADD CONSTRAINT `cathay_db_claim_cathay_head_fk` FOREIGN KEY (`cathay_head_id`) REFERENCES `cathay_head` (`id`),
  ADD CONSTRAINT `cathay_db_claim_cathay_history_fk` FOREIGN KEY (`cathay_history_id`) REFERENCES `cathay_history` (`id`);

--
-- Constraints for table `cathay_head`
--
ALTER TABLE `cathay_head`
  ADD CONSTRAINT `cathay_head_cathay_benefit_fk` FOREIGN KEY (`cathay_benefit_id`) REFERENCES `cathay_benefit` (`id`);

--
-- Constraints for table `cathay_history`
--
ALTER TABLE `cathay_history`
  ADD CONSTRAINT `cathay_history_provider_fk` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`);

--
-- Constraints for table `fubon_benefit`
--
ALTER TABLE `fubon_benefit`
  ADD CONSTRAINT `fubon_benefit_fubon_head_fk` FOREIGN KEY (`fubon_head_id`) REFERENCES `fubon_head` (`id`);

--
-- Constraints for table `fubon_db_claim`
--
ALTER TABLE `fubon_db_claim`
  ADD CONSTRAINT `fubon_db_claim_fubon_head_fk` FOREIGN KEY (`fubon_head_id`) REFERENCES `fubon_head` (`id`),
  ADD CONSTRAINT `fubon_db_claim_fubon_history_fk` FOREIGN KEY (`fubon_history_id`) REFERENCES `fubon_history` (`id`);

--
-- Constraints for table `fubon_head`
--
ALTER TABLE `fubon_head`
  ADD CONSTRAINT `fubon_head_fubon_benefit_fk` FOREIGN KEY (`fubon_benefit_id`) REFERENCES `fubon_benefit` (`id`);

--
-- Constraints for table `fubon_history`
--
ALTER TABLE `fubon_history`
  ADD CONSTRAINT `fubon_history_provider_fk` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`);

--
-- Constraints for table `lzafield`
--
ALTER TABLE `lzafield`
  ADD CONSTRAINT `lzafield_lzamodule_fk` FOREIGN KEY (`lzamodule_id`) REFERENCES `lzamodule` (`id`);

--
-- Constraints for table `lzafilter`
--
ALTER TABLE `lzafilter`
  ADD CONSTRAINT `lzafilter_lzafield_fk` FOREIGN KEY (`lzafield_id`) REFERENCES `lzafield` (`id`),
  ADD CONSTRAINT `lzafilter_lzamodule_fk` FOREIGN KEY (`lzamodule_id`) REFERENCES `lzamodule` (`id`),
  ADD CONSTRAINT `lzafilter_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `lzapermission`
--
ALTER TABLE `lzapermission`
  ADD CONSTRAINT `lzapermission_lzamodule_fk` FOREIGN KEY (`lzamodule_id`) REFERENCES `lzamodule` (`id`),
  ADD CONSTRAINT `lzapermission_lzarole_fk` FOREIGN KEY (`lzarole_id`) REFERENCES `lzarole` (`id`);

--
-- Constraints for table `lzasetting`
--
ALTER TABLE `lzasetting`
  ADD CONSTRAINT `lzasetting_lzasection_fk` FOREIGN KEY (`lzasection_id`) REFERENCES `lzasection` (`id`);

--
-- Constraints for table `lzastatistic`
--
ALTER TABLE `lzastatistic`
  ADD CONSTRAINT `lzastatistic_lzafield_fk` FOREIGN KEY (`lzafield_id`) REFERENCES `lzafield` (`id`),
  ADD CONSTRAINT `lzastatistic_lzamodule_fk` FOREIGN KEY (`lzamodule_id`) REFERENCES `lzamodule` (`id`),
  ADD CONSTRAINT `lzastatistic_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `lzaview`
--
ALTER TABLE `lzaview`
  ADD CONSTRAINT `lzaview_lzamodule_fk` FOREIGN KEY (`lzamodule_id`) REFERENCES `lzamodule` (`id`);

--
-- Constraints for table `mobile_claim`
--
ALTER TABLE `mobile_claim`
  ADD CONSTRAINT `mobile_claim_mobile_claim_status_fk` FOREIGN KEY (`mobile_claim_status_id`) REFERENCES `mobile_claim_status` (`id`),
  ADD CONSTRAINT `mobile_claim_mobile_user_bank_account_fk` FOREIGN KEY (`mobile_user_bank_account_id`) REFERENCES `mobile_user_bank_account` (`id`),
  ADD CONSTRAINT `mobile_claim_mobile_user_fk` FOREIGN KEY (`mobile_user_id`) REFERENCES `mobile_user` (`id`);

--
-- Constraints for table `mobile_claim_file`
--
ALTER TABLE `mobile_claim_file`
  ADD CONSTRAINT `mobile_claim_file_mobile_claim_fk` FOREIGN KEY (`mobile_claim_id`) REFERENCES `mobile_claim` (`id`);

--
-- Constraints for table `mobile_device`
--
ALTER TABLE `mobile_device`
  ADD CONSTRAINT `mobile_device_mobile_user_fk` FOREIGN KEY (`mobile_user_id`) REFERENCES `mobile_user` (`id`);

--
-- Constraints for table `mobile_user_bank_account`
--
ALTER TABLE `mobile_user_bank_account`
  ADD CONSTRAINT `mobile_user_bank_account_mobile_user_fk` FOREIGN KEY (`mobile_user_id`) REFERENCES `mobile_user` (`id`);

--
-- Constraints for table `pcv_benefit`
--
ALTER TABLE `pcv_benefit`
  ADD CONSTRAINT `pcv_benefit_pcv_head_fk` FOREIGN KEY (`pcv_head_id`) REFERENCES `pcv_head` (`id`);

--
-- Constraints for table `pcv_benefit_provider`
--
ALTER TABLE `pcv_benefit_provider`
  ADD CONSTRAINT `pcv_benefit_provider_pk_1` FOREIGN KEY (`pcv_benefit_id`) REFERENCES `pcv_benefit` (`id`),
  ADD CONSTRAINT `pcv_benefit_provider_pk_2` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`);

--
-- Constraints for table `pcv_db_claim`
--
ALTER TABLE `pcv_db_claim`
  ADD CONSTRAINT `pcv_db_claim_pcv_head_fk` FOREIGN KEY (`pcv_head_id`) REFERENCES `pcv_head` (`id`),
  ADD CONSTRAINT `pcv_db_claim_pcv_history_fk` FOREIGN KEY (`pcv_history_id`) REFERENCES `pcv_history` (`id`);

--
-- Constraints for table `pcv_head`
--
ALTER TABLE `pcv_head`
  ADD CONSTRAINT `pcv_head_pcv_benefit_fk` FOREIGN KEY (`pcv_benefit_id`) REFERENCES `pcv_benefit` (`id`);

--
-- Constraints for table `pcv_history`
--
ALTER TABLE `pcv_history`
  ADD CONSTRAINT `pcv_history_provider_fk` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_lzarole_fk` FOREIGN KEY (`lzarole_id`) REFERENCES `lzarole` (`id`),
  ADD CONSTRAINT `user_provider_fk` FOREIGN KEY (`provider_id`) REFERENCES `provider` (`id`);

DELIMITER $$
--
-- Events
--
DROP EVENT IF EXISTS `delete_expired_session`$$
CREATE DEFINER=`card_validation`@`localhost` EVENT `delete_expired_session` ON SCHEDULE EVERY 1 MINUTE STARTS '2021-01-12 15:05:12' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `mobile_user_session` WHERE `mobile_user_session`.`expire` < NOW()$$

DROP EVENT IF EXISTS `delete_expired_lzasession`$$
CREATE DEFINER=`card_validation`@`localhost` EVENT `delete_expired_lzasession` ON SCHEDULE EVERY 1 MINUTE STARTS '2021-01-12 15:10:09' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `lzasession` WHERE `crt_at` < NOW() - INTERVAL 1 HOUR$$

DROP EVENT IF EXISTS `delete_expired_otp`$$
CREATE DEFINER=`card_validation`@`localhost` EVENT `delete_expired_otp` ON SCHEDULE EVERY 1 MINUTE STARTS '2021-01-12 15:12:06' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `mobile_user_otp` WHERE `expire` < NOW()$$

DELIMITER ;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
