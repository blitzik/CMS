-- Adminer 4.2.2fx MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navigation` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_64C19C1493AC53F` (`navigation`),
  KEY `IDX_64C19C13D8E604F` (`parent`),
  KEY `lft` (`lft`),
  CONSTRAINT `FK_64C19C13D8E604F` FOREIGN KEY (`parent`) REFERENCES `category` (`id`),
  CONSTRAINT `FK_64C19C1493AC53F` FOREIGN KEY (`navigation`) REFERENCES `navigation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `category` (`id`, `navigation`, `parent`, `name`, `lft`, `rgt`, `depth`) VALUES
(1,	1,	NULL,	'Kategorie zboží',	1,	22,	0),
(2,	1,	1,	'Procesory',	2,	15,	1),
(3,	1,	2,	'Intel',	3,	8,	2),
(4,	1,	3,	'Pentium IV',	4,	5,	3),
(5,	1,	3,	'Celeron',	6,	7,	3),
(6,	1,	2,	'AMD',	9,	14,	2),
(7,	1,	6,	'Duron',	10,	11,	3),
(8,	1,	6,	'Athlon',	12,	13,	3),
(9,	1,	1,	'Paměti',	16,	21,	1),
(10,	1,	9,	'DDR',	17,	18,	2),
(11,	1,	9,	'DIMM',	19,	20,	2);

DROP TABLE IF EXISTS `navigation`;
CREATE TABLE `navigation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_493AC53F5E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 2016-01-11 15:21:55