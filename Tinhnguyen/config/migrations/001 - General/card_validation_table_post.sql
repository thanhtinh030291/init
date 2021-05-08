
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
CREATE TRIGGER `post__id` BEFORE INSERT ON `post` FOR EACH ROW BEGIN IF NEW.id IS NULL OR NEW.id = '' THEN SET NEW.id = UUID(); END IF; SET @last_uuid = NEW.id; END
$$
DELIMITER ;
