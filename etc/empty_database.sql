-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.21-MariaDB - MariaDB Server
-- Server OS:                    Linux
-- HeidiSQL Version:             9.4.0.5130
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table control_new.vhost
CREATE TABLE IF NOT EXISTS `vhost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `docroot` varchar(256) NOT NULL,
  `basedir` varchar(255) DEFAULT NULL,
  `servername` varchar(256) NOT NULL,
  `serveralias` tinytext NOT NULL,
  `serveradmin` varchar(64) NOT NULL,
  `custom` text NOT NULL COMMENT 'raw data, wordt toegevoegd achter aan de vhost (na eventuele redirect)',
  `cgi` tinyint(1) NOT NULL DEFAULT '1',
  `ssl` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: http, 1: https, 2:https with redirect',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  CONSTRAINT `vhost_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1526 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.ftp
CREATE TABLE IF NOT EXISTS `ftp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `passwd` varchar(128) NOT NULL,
  `dir` tinytext NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `uid` (`uid`),
  CONSTRAINT `ftp_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1462 DEFAULT CHARSET=latin1 COMMENT='virtuele ftp user tabel';

-- Data exporting was unselected.
-- Dumping structure for table control_new.geschiedenis
CREATE TABLE IF NOT EXISTS `geschiedenis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startdatum` date NOT NULL,
  `einddatum` date NOT NULL,
  `beschrijving` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.group
CREATE TABLE IF NOT EXISTS `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT 'x',
  `gid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gid` (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COMMENT='Holds group information for system';

-- Data exporting was unselected.
-- Dumping structure for table control_new.log
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `data` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_user_id_user_id` (`user_id`),
  CONSTRAINT `fk_log_user_id_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=696 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table control_new.mail_domain
CREATE TABLE IF NOT EXISTS `mail_domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `domain` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `uid` (`uid`),
  CONSTRAINT `mail_domain_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.mail_forward
CREATE TABLE IF NOT EXISTS `mail_forward` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `source` varchar(80) DEFAULT NULL,
  `mail_domain_id` int(11) DEFAULT NULL,
  `destination` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `mail_domain_id` (`mail_domain_id`),
  KEY `source` (`source`),
  CONSTRAINT `mail_forward_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE,
  CONSTRAINT `mail_forward_ibfk_2` FOREIGN KEY (`mail_domain_id`) REFERENCES `mail_domain` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.mail_user
CREATE TABLE IF NOT EXISTS `mail_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `email` varchar(80) NOT NULL,
  `mail_domain_id` int(11) DEFAULT NULL,
  `password` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `mail_domain_id` (`mail_domain_id`),
  KEY `email` (`email`),
  CONSTRAINT `mail_user_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE,
  CONSTRAINT `mail_user_ibfk_2` FOREIGN KEY (`mail_domain_id`) REFERENCES `mail_domain` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.medewerker
CREATE TABLE IF NOT EXISTS `medewerker` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `irc` varchar(32) NOT NULL,
  `status` varchar(32) DEFAULT NULL,
  `functie` tinytext,
  `intresses` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_UNIQUE` (`uid`),
  CONSTRAINT `medewerker_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.menuitem
CREATE TABLE IF NOT EXISTS `menuitem` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `parent` int(4) NOT NULL COMMENT '-1= not in menu, 0=menu header, else id of header',
  `name` varchar(32) NOT NULL COMMENT 'manu name',
  `url` varchar(128) DEFAULT NULL COMMENT 'url',
  `gid_access` int(11) DEFAULT '25',
  `order` tinyint(1) NOT NULL DEFAULT '0',
  `help` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `parent` (`parent`),
  KEY `menuitem_ibfk_1_idx` (`gid_access`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.page
CREATE TABLE IF NOT EXISTS `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `title` varchar(45) NOT NULL,
  `content` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `published` tinyint(1) DEFAULT '0' COMMENT '-1 = Niet gepubliceerd -- 0 = Niet in menu -- 1 = In menu',
  `weight` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for view control_new.sin_ftp
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `sin_ftp` (
	`user` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
	`passwd` VARCHAR(128) NOT NULL COLLATE 'latin1_swedish_ci',
	`uid` INT(11) NOT NULL,
	`gid` INT(11) NOT NULL COMMENT 'main group id',
	`dir` TINYTEXT NOT NULL COLLATE 'latin1_swedish_ci',
	`shell` VARCHAR(20) NOT NULL COMMENT 'shell van de user' COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

-- Data exporting was unselected.
-- Dumping structure for table control_new.system_task
CREATE TABLE IF NOT EXISTS `system_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) DEFAULT NULL,
  `data` text COMMENT 'json array',
  `start` int(10) DEFAULT NULL COMMENT 'unix timestamp',
  `end` int(10) DEFAULT NULL COMMENT 'unix timestamp',
  `interval` int(10) DEFAULT NULL COMMENT 'number of seconds',
  `exitcode` smallint(3) DEFAULT NULL COMMENT 'last exit code',
  `started` tinyint(1) NOT NULL DEFAULT '0',
  `lastRun` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'Global userid',
  `user_info_id` int(11) NOT NULL,
  `crypt` varchar(255) NOT NULL DEFAULT 'x' COMMENT 'hashed password',
  `gcos` varchar(255) NOT NULL COMMENT 'gcos field',
  `gid` int(11) NOT NULL DEFAULT '100' COMMENT 'main group id',
  `homedir` varchar(255) NOT NULL DEFAULT '' COMMENT 'homedir van de user',
  `shell` varchar(20) NOT NULL DEFAULT '/bin/bash' COMMENT 'shell van de user',
  `lastchange` bigint(20) NOT NULL DEFAULT '1',
  `min` bigint(20) NOT NULL DEFAULT '0',
  `max` bigint(20) NOT NULL DEFAULT '99999',
  `warn` bigint(20) NOT NULL DEFAULT '0',
  `inact` bigint(20) NOT NULL DEFAULT '0',
  `expire` bigint(20) DEFAULT NULL,
  `flag` bigint(20) unsigned NOT NULL DEFAULT '0',
  `smb_lm` varchar(255) NOT NULL,
  `smb_nt` varchar(255) NOT NULL,
  `diskusage` bigint(10) NOT NULL COMMENT 'diskusage, added by a script',
  `svnEnabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'enable=1, disable=0',
  `mailEnabled` tinyint(1) NOT NULL COMMENT 'enable=1, disable=0, blocked=-1',
  `remember_token` varchar(100) DEFAULT '' COMMENT 'remember me',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `user_ibfk_3` (`gid`),
  KEY `user_ibfk_2` (`user_info_id`),
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`user_info_id`) REFERENCES `user_info` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3083 DEFAULT CHARSET=latin1 COMMENT='Holds system group information';

-- Data exporting was unselected.
-- Dumping structure for table control_new.user_group
CREATE TABLE IF NOT EXISTS `user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_2` (`uid`,`gid`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`),
  CONSTRAINT `user_group_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `group` (`gid`),
  CONSTRAINT `user_group_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1 COMMENT='Links system users to system groups';

-- Data exporting was unselected.
-- Dumping structure for table control_new.user_info
CREATE TABLE IF NOT EXISTS `user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT 'Username',
  `fname` varchar(255) NOT NULL COMMENT 'Firstname',
  `lname` varchar(255) NOT NULL COMMENT 'Lastname',
  `email` varchar(45) NOT NULL COMMENT 'Email',
  `schoolnr` varchar(50) NOT NULL COMMENT 'Student number or Teachers number',
  `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `validated` tinyint(1) NOT NULL DEFAULT '0',
  `validationcode` varchar(64) DEFAULT NULL,
  `logintoken` varchar(64) DEFAULT NULL,
  `etc` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1456 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.user_limit
CREATE TABLE IF NOT EXISTS `user_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `ftp` int(5) NOT NULL DEFAULT '3',
  `mysql_user_max` int(5) NOT NULL DEFAULT '5',
  `mysql_db_max` int(5) NOT NULL DEFAULT '5',
  `vhost` int(5) NOT NULL DEFAULT '3',
  `mail_domain` int(5) NOT NULL DEFAULT '1',
  `mail_user` int(5) NOT NULL DEFAULT '3',
  `mail_forward` int(5) NOT NULL DEFAULT '3',
  `diskusage` int(6) NOT NULL DEFAULT '25000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  CONSTRAINT `user_limit_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table control_new.user_log
CREATE TABLE IF NOT EXISTS `user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_info_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nieuw` tinyint(1) NOT NULL DEFAULT '1',
  `boekhouding` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1 = Niet te factureren // 0 = Nog te factureren // 1 = Gefactureerd',
  PRIMARY KEY (`id`),
  KEY `fk_user_log_1_idx` (`user_info_id`),
  CONSTRAINT `fk_user_log_1` FOREIGN KEY (`user_info_id`) REFERENCES `user_info` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=646 DEFAULT CHARSET=latin1;

INSERT INTO `page` (`id`, `name`, `title`, `content`, `created_at`, `updated_at`, `deleted_at`, `published`, `weight`) VALUES (1, 'home', 'Home', '<h1>Welcome</h1>', '2014-09-10 12:08:07', '2014-12-12 10:50:27', NULL, 1, -127);


INSERT INTO `user_limit` (`id`, `uid`, `ftp`, `mysql_user_max`, `mysql_db_max`, `vhost`, `mail_domain`, `mail_user`, `mail_forward`, `diskusage`) VALUES (1, NULL, 5, 5, 3, 5, 2, 5, 5, 100000);


/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
