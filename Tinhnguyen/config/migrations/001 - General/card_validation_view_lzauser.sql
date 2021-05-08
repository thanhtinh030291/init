
-- --------------------------------------------------------

--
-- Structure for view `lzauser`
--
DROP TABLE IF EXISTS `lzauser`;

DROP VIEW IF EXISTS `lzauser`;
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`card_validation`@`localhost` SQL SECURITY DEFINER VIEW `lzauser`  AS SELECT `user`.`id` AS `id`, `user`.`crt_by` AS `crt_by`, `user`.`crt_at` AS `crt_at`, `user`.`upd_by` AS `upd_by`, `user`.`upd_at` AS `upd_at`, `user`.`lzarole_id` AS `lzarole_id`, `user`.`provider_id` AS `provider_id`, `user`.`username` AS `username`, `user`.`password` AS `password`, `user`.`fullname` AS `fullname`, `user`.`email` AS `email`, `user`.`is_admin` AS `is_admin`, `user`.`notify` AS `notify`, `user`.`enabled` AS `enabled`, `user`.`expiry` AS `expiry`, `user`.`last_reset_by` AS `last_reset_by`, `user`.`last_reset_at` AS `last_reset_at` FROM `user` ;
