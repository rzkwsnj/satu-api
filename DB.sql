-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.7.24 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for satu-api
CREATE DATABASE IF NOT EXISTS `satu-api` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `satu-api`;

-- Dumping structure for table satu-api.rzkwsnj_roles
CREATE TABLE IF NOT EXISTS `rzkwsnj_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `role_desc` varchar(255) NOT NULL,
  `role_permission` text,
  `register_default` int(1) NOT NULL DEFAULT '0',
  `dentist_default` int(1) NOT NULL DEFAULT '0',
  `patient_default` int(1) NOT NULL DEFAULT '0',
  `admin_default` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- Dumping data for table satu-api.rzkwsnj_roles: ~7 rows (approximately)
/*!40000 ALTER TABLE `rzkwsnj_roles` DISABLE KEYS */;
INSERT INTO `rzkwsnj_roles` (`role_id`, `role_name`, `role_desc`, `role_permission`, `register_default`, `dentist_default`, `patient_default`, `admin_default`) VALUES
	(1, 'Super Admin', 'Super Admin', '{"dashboard":"1","settings":"1"}', 0, 0, 0, 1),
	(2, 'Normal Admin', 'Normal Admin', '{"dashboard":"1","settings":"0"}', 0, 0, 0, 1);
/*!40000 ALTER TABLE `rzkwsnj_roles` ENABLE KEYS */;

-- Dumping structure for table satu-api.rzkwsnj_users
CREATE TABLE IF NOT EXISTS `rzkwsnj_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `session_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'stores session cookie id to prevent session concurrency',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s name, unique',
  `user_password_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s password in salted and hashed format',
  `user_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s email, unique',
  `user_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s activation status',
  `user_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s deletion status',
  `user_account_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'user''s account type (basic, premium, etc)',
  `user_has_avatar` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if user has a local avatar, 0 if not',
  `user_creation_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the creation of user''s account',
  `user_suspension_timestamp` bigint(20) DEFAULT NULL COMMENT 'Timestamp till the end of a user suspension',
  `user_last_login_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of user''s last login',
  `user_failed_logins` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s failed login attempts',
  `user_last_failed_login` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unix timestamp of last failed login attempt',
  `user_activation_hash` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s email verification hash string',
  `user_password_reset_hash` char(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s password reset code',
  `user_password_reset_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the password reset request',
  `user_provider_type` text COLLATE utf8_unicode_ci,
  `role_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data';

-- Dumping data for table satu-api.rzkwsnj_users: ~0 rows (approximately)
/*!40000 ALTER TABLE `rzkwsnj_users` DISABLE KEYS */;
INSERT INTO `rzkwsnj_users` (`user_id`, `session_id`, `user_name`, `user_password_hash`, `user_email`, `user_active`, `user_deleted`, `user_account_type`, `user_has_avatar`, `user_creation_timestamp`, `user_suspension_timestamp`, `user_last_login_timestamp`, `user_failed_logins`, `user_last_failed_login`, `user_activation_hash`, `user_password_reset_hash`, `user_password_reset_timestamp`, `user_provider_type`, `role_id`) VALUES
	(1, '', 'demo', '$2y$10$OvprunjvKOOhM1h9bzMPs.vuwGIsOqZbw88rzSyGCTJTcE61g5WXi', 'drg.rizkiwisnuaji@gmail.com', 1, 0, 7, 1, 1422205178, NULL, 1630077109, 0, NULL, NULL, NULL, NULL, 'DEFAULT', 1);
/*!40000 ALTER TABLE `rzkwsnj_users` ENABLE KEYS */;

-- Dumping structure for table satu-api.rzkwsnj_user_profile
CREATE TABLE IF NOT EXISTS `rzkwsnj_user_profile` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birth_date` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8_unicode_ci,
  `address` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `rzkwsnj_user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rzkwsnj_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table satu-api.rzkwsnj_user_profile: ~0 rows (approximately)
/*!40000 ALTER TABLE `rzkwsnj_user_profile` DISABLE KEYS */;
INSERT INTO `rzkwsnj_user_profile` (`user_id`, `first_name`, `last_name`, `birth_date`, `phone`, `bio`, `address`) VALUES
	(1, 'Rizki', 'Wisnuaji', '17-08-1945', '(+62) 022 2506902', 'Make IT Easier', 'Bandung');
/*!40000 ALTER TABLE `rzkwsnj_user_profile` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
