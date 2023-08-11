-- default user  architect : kreslo

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `password` char(60) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `role` varchar(30) COLLATE utf8_czech_ci NOT NULL DEFAULT 'user',
  `active` char(1) COLLATE utf8_czech_ci NOT NULL DEFAULT '1',
  `name` varchar(250) COLLATE utf8_czech_ci NOT NULL,
  `avatar` varchar(250) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `active`, `name`, `avatar`) VALUES
(1, 'architect',  '$2y$10$K.JEAIhI/bmk2Kas2uxFi.3Y.qJ6LZNw44X5k9Lq81R27wgcZqsSu', 'info@aprila.cz', 'root', '1',  'Architect',  '');

DROP TABLE IF EXISTS `users_password_reset`;
CREATE TABLE `users_password_reset` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `salt` char(32) COLLATE utf8_czech_ci NOT NULL,
  `token` char(64) COLLATE utf8_czech_ci NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `userId` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- change email
ALTER TABLE `users`
ADD `change_email_requested` datetime NOT NULL,
ADD `change_email_tokenOne` char(60) COLLATE 'utf8_czech_ci' NOT NULL AFTER `change_email_requested`,
ADD `change_email_tokenTwo` char(60) COLLATE 'utf8_czech_ci' NOT NULL AFTER `change_email_tokenOne`,
COMMENT='';

ALTER TABLE `users`
ADD `change_email` varchar(255) COLLATE 'utf8_czech_ci' NOT NULL AFTER `avatar`,
COMMENT='';