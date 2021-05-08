
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
