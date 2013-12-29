-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 29. Dez 2013 um 17:39
-- Server Version: 5.5.16
-- PHP-Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `foundation`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_attachments`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `file` varchar(300) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Daten für Tabelle `pp_bookie_attachments`
--

INSERT INTO `pp_bookie_attachments` (`id`, `entry_id`, `date`, `file`, `file_type`) VALUES
(1, 9, '2013-12-28', 'o_18ctlc7g11fsha5nrdguk84hg1i.jpg', ''),
(2, 9, '2013-12-29', 'o_18ctn0gqt3tq7e710jr10na1fm71i.jpg', ''),
(3, 9, '2013-12-29', 'o_18ctn2c9t15ve13ctdr5c90sng1i.jpg', ''),
(4, 9, '2013-12-29', 'o_18ctn384k1hr1g82dr1baajke1i.jpg', ''),
(5, 9, '2013-12-29', 'o_18ctnarub1nq21f9e1a5418cklnj1i.jpg', ''),
(6, 9, '2013-12-29', 'o_18ctnbphe1i62f6ugb3o44q5j1k.jpg', ''),
(7, 7, '2013-12-29', 'o_18ctnd6b21htneqlbf81rifplq1i.jpg', ''),
(8, 2, '2013-12-29', 'o_18ctnde1k1q561jfqekb75ui6q5n.jpg', ''),
(9, 8, '2013-12-29', 'o_18ctnd8vuf1iui4fnqpm2tr2s.jpg', ''),
(10, 2, '2013-12-29', 'o_18ctnde1k1t39q15hdq139ute25o.jpg', ''),
(11, 7, '2013-12-29', 'o_18ctne8hb1alnajcs543uokfo1i.jpg', ''),
(12, 7, '2013-12-29', 'o_18ctng5gv17lu1l9m19cd1cc11e8f1i.jpg', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_entries`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notes` text NOT NULL,
  `brutto` decimal(10,2) NOT NULL,
  `netto` decimal(10,2) NOT NULL,
  `tax_type` varchar(30) NOT NULL,
  `tax_value` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `state` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `pp_bookie_entries`
--

INSERT INTO `pp_bookie_entries` (`id`, `user_id`, `notes`, `brutto`, `netto`, `tax_type`, `tax_value`, `date`, `state`) VALUES
(1, 24, 'Test', '100.00', '100.00', '', '0.00', '2013-12-15', 'payed'),
(2, 24, 'Test 2', '-100.00', '-83.33', 'MwSt.', '0.20', '2013-12-15', 'delayed'),
(4, 24, 'asdf', '4593.50', '3827.92', 'MwSt.', '0.20', '2013-12-17', 'dunned'),
(5, 24, '', '208.33', '208.33', 'MwSt.', '0.20', '2013-12-12', 'payed'),
(6, 24, '', '333.33', '333.33', 'Ust.', '0.00', '2013-12-04', 'open'),
(7, 24, 'so ein depp', '0.00', '-400.00', 'Deppensteuer', '0.30', '2013-12-20', 'payed'),
(8, 24, '', '500.00', '416.67', 'MwSt.', '0.20', '2013-12-19', 'open'),
(9, 24, '', '239.00', '199.17', '', '0.20', '2013-12-23', 'open');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_entry_contacts`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_entry_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry_id` (`entry_id`,`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Daten für Tabelle `pp_bookie_entry_contacts`
--

INSERT INTO `pp_bookie_entry_contacts` (`id`, `entry_id`, `contact_id`) VALUES
(1, 1, 5),
(2, 1, 8),
(14, 1, 16),
(3, 5, 13),
(9, 7, 11),
(10, 8, 13),
(11, 9, 11),
(12, 10, 8);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_invoices`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `alt_dst_adr` varchar(200) NOT NULL,
  `alt_src_adr` varchar(200) NOT NULL,
  `number` varchar(100) NOT NULL,
  `pay_date` date NOT NULL,
  `reminder_date` datetime NOT NULL,
  `dunnings` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_invoice_parts`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_invoice_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `notes` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_receipts`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `number` varchar(100) NOT NULL,
  `account` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_calendar_events`
--

CREATE TABLE IF NOT EXISTS `pp_calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `text` varchar(255) NOT NULL,
  `owner_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `pp_calendar_events`
--

INSERT INTO `pp_calendar_events` (`id`, `start_date`, `end_date`, `text`, `owner_id`) VALUES
(2, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'blubb', 24),
(3, '2013-12-26 16:10:00', '2013-12-26 22:05:00', 'tadamm', 24),
(4, '2013-12-26 10:30:00', '2013-12-26 14:45:00', 'ehehehe', 24);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Daten für Tabelle `pp_contactdata`
--

INSERT INTO `pp_contactdata` (`id`, `contact_id`, `key`, `value`) VALUES
(1, 8, 'testfeld', 'ahahahahha'),
(4, 0, 'Nähzeugs', 'Nadel & Zwirn'),
(5, 15, 'Nähzeugs', 'Nadel & Zwirn'),
(6, 16, 'asdf', 'asdasd');

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
  `pc` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `notes` text NOT NULL,
  `last_contact` datetime NOT NULL,
  `ssnum` varchar(20) NOT NULL,
  `image` varchar(200) NOT NULL,
  `birthdate` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Daten für Tabelle `pp_contacts`
--

INSERT INTO `pp_contacts` (`id`, `user_id`, `firstname`, `lastname`, `address`, `pc`, `city`, `email`, `phone`, `notes`, `last_contact`, `ssnum`, `image`, `birthdate`) VALUES
(5, 24, 'Max', 'Muster', '', '', '', 'max@muster.com', '1234566789', '', '0000-00-00 00:00:00', '', '', '1981-07-29'),
(6, 24, 'Thomas', 'Anders', '', '1234', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00'),
(7, 24, 'Max', 'Peters', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00'),
(8, 24, 'Sabine', 'Arnautovic', 'Testingerstraße 23', '1234', 'Wien', 'sabine.a@gmail.com', '', 'asdf', '0000-00-00 00:00:00', '', 'o_18cbjracp1kghe2915131h0r157ee.jpg', '0000-00-00'),
(9, 24, 'Thomas', 'Breuer', '', '', '', '', '0000 000 0', '', '0000-00-00 00:00:00', '', '', '0000-00-00'),
(10, 24, 'Moritz', 'Kern', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00'),
(11, 24, 'Kevin', 'Tran', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00'),
(12, 24, 'Thomas', 'Albrecht', '', '', '', '', '', '', '0000-00-00 00:00:00', '', 'o_18cvds99g1eqd112e1rm31m7c1hp48.jpg', '2013-12-29'),
(13, 24, 'Maxi', 'Müller', '', '1234', 'Wien', '', '', '', '0000-00-00 00:00:00', '', 'o_18cbk05ml1v9137o1aa51h1mqn10.jpg', '0000-00-00'),
(14, 24, 'Maxi', 'Muster', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00'),
(15, 24, 'Igor', 'Igor', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00'),
(16, 24, 'Maxi', 'Müllersen', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00');

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
(1, 'root', 'a3597971769fc171e38fb92ff3cd4cc429370b618342836ff7a2eb61fe7d6f70ead7dd6586c2044d759ab962b6fbb96d48981259e592e3c79b559d84a79fe64a#me:fpeH2cc68;p9npeQ/Qemi0UQ%Wu!g4Hweu=US4JsPUxqa-Oe', 1, 'root@apple.com', '', '', 1, 0, 1387833730),
(24, 'tester', 'c708109949e40eb065844fad07f90e45114c1f3a5beb06b5a451855894b9f7a8aa7a72903e761d67ec9ea30269abeef15989dd4ce931d8f853a0468c12be154c#d3x,Zgv2F|;dntc5Zq_YUuucom!|R6tB5j5dj$VxmUaj8fNhAYG', 3, 'lol@test.com', '', '', 1, 1385307568, 1388334974),
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Daten für Tabelle `pp_userdata`
--

INSERT INTO `pp_userdata` (`id`, `user_id`, `field_id`, `value`, `last_change`) VALUES
(1, 19, 1, 'Tester', '2013-11-30 21:53:26'),
(2, 19, 2, 'McTestinger', '2013-11-30 21:53:26'),
(3, 20, 1, '', '2013-11-30 21:53:54'),
(4, 20, 2, '', '2013-11-30 21:53:54'),
(5, 24, 1, 'Tester', '2013-12-23 22:22:23'),
(6, 24, 2, 'McTest', '2013-12-23 22:22:23');

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
