
DELIMITER $$
--
-- Events
--
DROP EVENT `delete_expired_session`$$
CREATE DEFINER=`card_validation`@`localhost` EVENT `delete_expired_session` ON SCHEDULE EVERY 1 MINUTE STARTS '2021-01-12 15:05:12' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `mobile_user_session` WHERE `mobile_user_session`.`expire` < NOW()$$

DROP EVENT `delete_expired_lzasession`$$
CREATE DEFINER=`card_validation`@`localhost` EVENT `delete_expired_lzasession` ON SCHEDULE EVERY 1 MINUTE STARTS '2021-01-12 15:10:09' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `lzasession` WHERE `crt_at` < NOW() - INTERVAL 1 HOUR$$

DROP EVENT `delete_expired_otp`$$
CREATE DEFINER=`card_validation`@`localhost` EVENT `delete_expired_otp` ON SCHEDULE EVERY 1 MINUTE STARTS '2021-01-12 15:12:06' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `mobile_user_otp` WHERE `expire` < NOW()$$

DELIMITER ;
