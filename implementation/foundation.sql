-- phpMyAdmin SQL Dump
-- version 4.0.3
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 22. Nov 2013 um 15:13
-- Server Version: 5.6.11-log
-- PHP-Version: 5.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `foundation`
--
CREATE DATABASE IF NOT EXISTS `foundation` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `foundation`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_rights`
--

CREATE TABLE IF NOT EXISTS `pp_rights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `service` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`service`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Daten für Tabelle `pp_rights`
--

INSERT INTO `pp_rights` (`id`, `name`, `service`) VALUES
(1, 'usercenter', 'User'),
(2, 'administer_user', 'User'),
(3, 'administer_group', 'User'),
(4, 'edit_group', 'User'),
(5, 'create_groups', 'User'),
(6, 'create_data', 'User'),
(7, 'can_change_viewing_user', 'User'),
(8, 'administer_settings', 'Settings'),
(9, 'administer_hidden_settings', 'Settings'),
(10, 'administer_tags', 'Tags');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_right_group`
--

CREATE TABLE IF NOT EXISTS `pp_right_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  `param` varchar(300) NOT NULL,
  `auth` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `relation` (`group_id`,`right_id`,`param`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Daten für Tabelle `pp_right_group`
--

INSERT INTO `pp_right_group` (`id`, `group_id`, `right_id`, `param`, `auth`) VALUES
(1, 1, 1, '', 1),
(2, 1, 2, '', 1),
(3, 1, 3, '', 1),
(4, 1, 4, '', 1),
(5, 1, 5, '', 1),
(6, 1, 6, '', 1),
(7, 1, 7, '', 1),
(8, 1, 8, '', 1),
(9, 1, 9, '', 1),
(10, 1, 10, '', 1),
(11, 2, 1, '', 1),
(12, 2, 2, '', 1),
(13, 2, 3, '', 1),
(14, 2, 4, '', 1),
(15, 2, 5, '', 1),
(16, 2, 6, '', 1),
(17, 2, 7, '', 1),
(18, 2, 8, '', 1),
(19, 2, 9, '', 1),
(20, 2, 10, '', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_right_user`
--

CREATE TABLE IF NOT EXISTS `pp_right_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  `param` varchar(300) NOT NULL,
  `auth` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`right_id`,`param`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_tags`
--

CREATE TABLE IF NOT EXISTS `pp_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `webname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_tags_link`
--

CREATE TABLE IF NOT EXISTS `pp_tags_link` (
  `id` int(11) NOT NULL,
  `service` varchar(100) NOT NULL,
  `param` varchar(100) NOT NULL,
  UNIQUE KEY `id` (`id`,`service`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_user`
--

CREATE TABLE IF NOT EXISTS `pp_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(50) NOT NULL,
  `hash` varchar(180) NOT NULL,
  `group` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `activate` varchar(32) NOT NULL,
  `reset` varchar(32) NOT NULL,
  `status` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `pp_user`
--

INSERT INTO `pp_user` (`id`, `nick`, `hash`, `group`, `email`, `activate`, `reset`, `status`, `created`, `last_login`) VALUES
(1, 'root', 'a3597971769fc171e38fb92ff3cd4cc429370b618342836ff7a2eb61fe7d6f70ead7dd6586c2044d759ab962b6fbb96d48981259e592e3c79b559d84a79fe64a#me:fpeH2cc68;p9npeQ/Qemi0UQ%Wu!g4Hweu=US4JsPUxqa-Oe', 1, 'root@apple.com', '', '', 1, 0, 1347803752);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_userdata`
--

CREATE TABLE IF NOT EXISTS `pp_userdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `value` text NOT NULL,
  `last_change` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_userdatafield`
--

CREATE TABLE IF NOT EXISTS `pp_userdatafield` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `info` text NOT NULL,
  `type` int(11) NOT NULL,
  `group` int(11) NOT NULL,
  `vis_register` int(1) NOT NULL COMMENT 'visibility login 0-2',
  `vis_login` int(1) NOT NULL COMMENT 'visibility register 0-2',
  `vis_edit` int(1) NOT NULL COMMENT 'visibility edit 0-2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_userdatagroup`
--

CREATE TABLE IF NOT EXISTS `pp_userdatagroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_usergroup`
--

CREATE TABLE IF NOT EXISTS `pp_usergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `pp_usergroup`
--

INSERT INTO `pp_usergroup` (`id`, `name`) VALUES
(1, 'root'),
(2, 'admin'),
(3, 'user'),
(4, 'moderator'),
(5, 'guest');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
