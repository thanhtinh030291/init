
-- --------------------------------------------------------

--
-- Structure for view `user_module`
--
DROP TABLE IF EXISTS `user_module`;

DROP VIEW IF EXISTS `user_module`;
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`card_validation`@`localhost` SQL SECURITY DEFINER VIEW `user_module`  AS SELECT `p`.`username` AS `username`, `p`.`level` AS `level`, `m`.`id` AS `id`, `m`.`db_id` AS `db_id`, `m`.`id` AS `name`, `m`.`parent` AS `parent`, `m`.`icon` AS `icon`, `m`.`single` AS `single`, `m`.`plural` AS `plural`, `m`.`single_vi` AS `single_vi`, `m`.`plural_vi` AS `plural_vi`, `m`.`note` AS `note`, `m`.`display` AS `display`, `m`.`sort` AS `sort`, `m`.`enabled` AS `enabled`, `m`.`settings` AS `settings`, `m`.`order_by` AS `order_by` FROM (`user_permission` `p` join `lzamodule` `m` on(`p`.`module_id` = `m`.`id`)) ;
