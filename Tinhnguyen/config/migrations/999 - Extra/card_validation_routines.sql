
DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `add_column_if_not_exists`$$
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `add_column_if_not_exists` (IN `in_table` VARCHAR(128), IN `in_column` VARCHAR(4096))  BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `drop_column_if_exists` (IN `in_table` VARCHAR(128), IN `in_column` VARCHAR(128))  BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `drop_index_if_exists` (IN `in_table` VARCHAR(128), IN `in_index` VARCHAR(128))  BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` PROCEDURE `modify_column_if_exists` (IN `in_table` VARCHAR(128), IN `in_column` VARCHAR(4096))  BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `from_db_date` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS VARCHAR(20) CHARSET utf8 BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `from_db_datetime` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS VARCHAR(20) CHARSET utf8 BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_datetime_format` () RETURNS VARCHAR(30) CHARSET utf8 BEGIN
    DECLARE RESULT VARCHAR(30);

    SELECT `value`
    FROM `lzasetting`
    WHERE `key` = 'datetime_format'
    INTO RESULT;

    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `get_date_format`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_date_format` () RETURNS VARCHAR(30) CHARSET utf8 BEGIN
    DECLARE RESULT VARCHAR(30);

    SELECT `value`
    FROM `lzasetting`
    WHERE `key` = 'date_format'
    INTO RESULT;

    RETURN RESULT;
END$$

DROP FUNCTION IF EXISTS `get_fubon_amt_limit_per_dis_day`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_fubon_amt_limit_per_dis_day` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3)) RETURNS INT(10) UNSIGNED BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_fubon_amt_limit_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3)) RETURNS INT(10) UNSIGNED BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_fubon_ben_head` (`in_ben_head` VARCHAR(14)) RETURNS VARCHAR(14) CHARSET utf8 BEGIN
    DECLARE `ben_head` VARCHAR(14);

    SELECT `code`
    FROM `fubon_head`
    WHERE `code` = `in_ben_head`
    INTO `ben_head`;

    RETURN IFNULL(`ben_head`,'OPALL');
END$$

DROP FUNCTION IF EXISTS `get_pcv_amt_limit_per_year`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_pcv_amt_limit_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3)) RETURNS INT(10) UNSIGNED BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_pcv_ben_head` (`in_ben_head` VARCHAR(14)) RETURNS VARCHAR(14) CHARSET utf8 BEGIN
    DECLARE `ben_head` VARCHAR(14);

    SELECT `code`
    FROM `pcv_head`
    WHERE `code` = `in_ben_head`
    INTO `ben_head`;

    RETURN IFNULL(`ben_head`,'OPALL');
END$$

DROP FUNCTION IF EXISTS `get_pcv_plan_desc`$$
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_pcv_plan_desc` (`in_plan_desc` VARCHAR(255)) RETURNS VARCHAR(1000) CHARSET utf8 BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_remain_fubon_amt_per_dis_day` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED, `in_diag_id` TINYINT(3) UNSIGNED) RETURNS INT(10) BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_remain_fubon_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_remain_pcv_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_used_fubon_amt_per_dis_day` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED, `in_diag_id` TINYINT(3) UNSIGNED) RETURNS INT(10) UNSIGNED BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_used_fubon_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) UNSIGNED BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `get_used_pcv_amt_per_year` (`in_mbr_no` VARCHAR(14), `in_incur_date` DATE, `in_ben_id` TINYINT(3) UNSIGNED) RETURNS INT(10) UNSIGNED BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `initcap` (`x` CHAR(255)) RETURNS CHAR(255) CHARSET utf8 BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `to_db_date` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS DATE BEGIN
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
CREATE DEFINER=`card_validation`@`localhost` FUNCTION `to_db_datetime` (`in_date` CHAR(20), `in_format` CHAR(20)) RETURNS DATETIME BEGIN
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
