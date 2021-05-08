
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
('4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d5f17-4bf0-11eb-8142-98fa9b10d0b1'),
('408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d5f17-4bf0-11eb-8142-98fa9b10d0b1'),
('4086eadc-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d7fc1-4bf0-11eb-8142-98fa9b10d0b1'),
('408773a2-4a6f-11eb-a7cf-98fa9b10d0b1', 'fd2d7fc1-4bf0-11eb-8142-98fa9b10d0b1');
