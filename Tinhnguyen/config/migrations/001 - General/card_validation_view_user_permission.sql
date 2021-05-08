
-- --------------------------------------------------------

--
-- Structure for view `user_permission`
--
DROP TABLE IF EXISTS `user_permission`;

DROP VIEW IF EXISTS `user_permission`;
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`card_validation`@`localhost` SQL SECURITY DEFINER VIEW `user_permission`  AS SELECT `u`.`username` AS `username`, `m`.`id` AS `module_id`, `p`.`level` AS `level` FROM (((`user` `u` join `lzarole` `r` on(`u`.`lzarole_id` = `r`.`id`)) join `lzapermission` `p` on(`p`.`lzarole_id` = `r`.`id`)) join `lzamodule` `m` on(`p`.`lzamodule_id` = `m`.`id`)) ;
