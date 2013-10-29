-- Adminer 3.7.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+01:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELIMITER ;;

DROP PROCEDURE IF EXISTS `validate_user`;;
CREATE PROCEDURE `validate_user`(IN `in_id` tinyint(10) unsigned, IN `in_email` varchar(200) CHARACTER SET 'utf8', IN `in_loginName` varchar(200) CHARACTER SET 'utf8')
BEGIN
	IF NOT (SELECT in_email REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$')
	THEN
		SIGNAL SQLSTATE '45003' 
		SET MESSAGE_TEXT = 'Cannot add or update row: wrong email format';
    	END IF;

	IF (SELECT COUNT(id) FROM user WHERE id != in_id AND email = in_email) > 0
	THEN
		SIGNAL SQLSTATE '45001'
		SET MESSAGE_TEXT = 'Cannot add or update row: email already exist';
	END IF;

	IF (SELECT COUNT(id) FROM user WHERE id != in_id AND loginName = in_loginName) > 0
  	THEN
		SIGNAL SQLSTATE '45002'
		SET MESSAGE_TEXT = 'Cannot add or update row: loginName  already exist';
	END IF;
END;;

DELIMITER ;

DROP TABLE IF EXISTS `logger`;
CREATE TABLE `logger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `message` text NOT NULL,
  `ip` varchar(200) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `priority` int(100) NOT NULL,
  `exception` text,
  `exceptionFilename` text,
  `identifer` varchar(200) NOT NULL,
  `args` text,
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `logger_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `report`;
CREATE TABLE `report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reportDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idIssue` text,
  `description` text,
  `timeRequired` float DEFAULT NULL,
  `timeSpend` float DEFAULT NULL,
  `taskCompleted` int(11) DEFAULT NULL,
  `dateAdded` timestamp NULL DEFAULT NULL,
  `dateModified` timestamp NULL DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `report_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;

CREATE TRIGGER `report_before_insert` BEFORE INSERT ON `report` FOR EACH ROW
BEGIN
    SET NEW.dateAdded = CURRENT_TIMESTAMP();
END;;

CREATE TRIGGER `report_before_update` BEFORE UPDATE ON `report` FOR EACH ROW
BEGIN
    SET NEW.dateModified = CURRENT_TIMESTAMP();
END;;

DELIMITER ;

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `role` (`id`, `name`) VALUES
(1,	'superAdmin');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `loginName` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user` (`id`, `email`, `loginName`, `password`, `active`) VALUES
(1,	'admin@admin.com',	'admin',	'$2a$07$lyq51f3huytcpj20xpoonuV5xDMxaxr96L8ZAo4.uOS4WKG0AIdBe',	1);

DELIMITER ;;

CREATE TRIGGER `user_before_insert` BEFORE INSERT ON `user` FOR EACH ROW
BEGIN
	CALL validate_user(NEW.id, NEW.email, NEW.loginName);
END;;

CREATE TRIGGER `user_before_update` BEFORE UPDATE ON `user` FOR EACH ROW
BEGIN
	CALL validate_user(NEW.id, NEW.email, NEW.loginName);
END;;

DELIMITER ;

DROP TABLE IF EXISTS `userrole`;
CREATE TABLE `userrole` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `userrole_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userrole_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `userrole` (`id`, `user_id`, `role_id`) VALUES
(1,	1,	1);

-- 2013-10-29 23:58:33
