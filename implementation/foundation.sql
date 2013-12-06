-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 06. Dez 2013 um 23:40
-- Server Version: 5.5.16
-- PHP-Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `foundation`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_contactdata`
--

CREATE TABLE IF NOT EXISTS `pp_contactdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL,
  `key` varchar(200) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_id` (`contact_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_contacts`
--

CREATE TABLE IF NOT EXISTS `pp_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `address` varchar(200) NOT NULL,
  `pc` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `notes` text NOT NULL,
  `last_contact` datetime NOT NULL,
  `ssnum` int(11) NOT NULL,
  `image` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

--
-- Daten für Tabelle `pp_user`
--

INSERT INTO `pp_user` (`id`, `nick`, `hash`, `group`, `email`, `activate`, `reset`, `status`, `created`, `last_login`) VALUES
(1, 'root', 'a3597971769fc171e38fb92ff3cd4cc429370b618342836ff7a2eb61fe7d6f70ead7dd6586c2044d759ab962b6fbb96d48981259e592e3c79b559d84a79fe64a#me:fpeH2cc68;p9npeQ/Qemi0UQ%Wu!g4Hweu=US4JsPUxqa-Oe', 1, 'root@apple.com', '', '', 1, 0, 1347803752),
(24, 'tester', 'c708109949e40eb065844fad07f90e45114c1f3a5beb06b5a451855894b9f7a8aa7a72903e761d67ec9ea30269abeef15989dd4ce931d8f853a0468c12be154c#d3x,Zgv2F|;dntc5Zq_YUuucom!|R6tB5j5dj$VxmUaj8fNhAYG', 1, 'lol@test.com', '', '', 1, 1385307568, 1386364276),
(23, 'tester1', '671ccdec908af53b2701e800c40695fff0db7970495fa13448fb2aecb7cfefe15feb100679ea1d559ff08ae707c96fb4c36f7af12204a3d24acbc69390456778#yTlji4B7C\\!klsnm4-J3i\\X14iDsuXiA$3.NkmL$fLLddutwoZf', 1, 'lol@test.com', '', '', 1, 1385307568, -1),
(22, 'tester2', 'a929adf5e33f76c17fbc1084b26e6ba234804b40808ba33f78c08aea044c38ee2b77ed0ed2978d3e85a479067d22a1454c8e66ca5488331b7458ab4bdd1b97a7#XjUOu@6jWEKf$ci$Ydxp:V3o52ah5&tN5hmNikzk%?q5pFeWhIg', 1, 'lol@test.com', '', '', 1, 1385307567, -1),
(21, 'tester3', 'e34f95d898907dc4a1bebd377679ea945b3854c3879fbc0df459551690cfb4017419f665ad79b6aae3f207771962592e18ac2e24a72f78d0650c5f220b1f64f6#KwnsI1fdYQk2M5u2\\9lBlXwHzmo5|xG?,+mExwudiU%dcsh7PY$', 1, 'lol@test.com', '', '', 1, 1385307566, -1),
(20, 'tester4', 'eb60f595c996a0dd9837577f642829e0d83d029546eb91d69cad066297ffddcf4b470684acb34df1551ff099ae167385b128eb8c986e9c4db58c8a00b023d255#Mv5lH5q*si6dkGLzud\\r:bU6RRp)nek5pyx3tZdYzq(Kx*W%4MO', 1, 'loler@test.com', '', '', 1, 1385307566, -1),
(19, 'tester5', '72b56bb632fda7b56e1f779945377271b84f4ee29afd8f5b7011c523de5c83648a7fd0706864bf41121ad0511f5f40124dc6a32766d1737fe9c5aeb4046fcb5e#%&wz7y2!KKgvCNcepzb3alpP59V_\\g:IgynxAfKbURs+W6eH4xn', 1, 'yadada@lol.com', '', '', 1, 1385306186, -1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `pp_userdata`
--

INSERT INTO `pp_userdata` (`id`, `user_id`, `field_id`, `value`, `last_change`) VALUES
(1, 19, 1, 'Tester', '2013-11-30 21:53:26'),
(2, 19, 2, 'McTestinger', '2013-11-30 21:53:26'),
(3, 20, 1, '', '2013-11-30 21:53:54'),
(4, 20, 2, '', '2013-11-30 21:53:54');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `pp_userdatafield`
--

INSERT INTO `pp_userdatafield` (`id`, `name`, `info`, `type`, `group`, `vis_register`, `vis_login`, `vis_edit`) VALUES
(1, 'vname', 'Vorname', 0, 0, 1, 0, 1),
(2, 'nname', 'Nachname', 0, 0, 1, 0, 1);

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
