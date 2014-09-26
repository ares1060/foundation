-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 26. Sep 2014 um 20:56
-- Server Version: 5.5.16
-- PHP-Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `foundation`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_attachments`
--

CREATE TABLE IF NOT EXISTS `pp_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(100) NOT NULL,
  `param` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `file` varchar(300) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_name` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;

--
-- Daten für Tabelle `pp_attachments`
--

INSERT INTO `pp_attachments` (`id`, `service`, `param`, `date`, `file`, `file_type`, `file_name`) VALUES
(1, 'Bookie', '9', '2013-12-28', 'o_18ctlc7g11fsha5nrdguk84hg1i.jpg', '', ''),
(2, 'Bookie', '9', '2013-12-29', 'o_18ctn0gqt3tq7e710jr10na1fm71i.jpg', '', ''),
(3, 'Bookie', '9', '2013-12-29', 'o_18ctn2c9t15ve13ctdr5c90sng1i.jpg', '', ''),
(4, 'Bookie', '9', '2013-12-29', 'o_18ctn384k1hr1g82dr1baajke1i.jpg', '', ''),
(5, 'Bookie', '9', '2013-12-29', 'o_18ctnarub1nq21f9e1a5418cklnj1i.jpg', '', ''),
(6, 'Bookie', '9', '2013-12-29', 'o_18ctnbphe1i62f6ugb3o44q5j1k.jpg', '', ''),
(7, 'Bookie', '7', '2013-12-29', 'o_18ctnd6b21htneqlbf81rifplq1i.jpg', '', ''),
(9, 'Bookie', '8', '2013-12-29', 'o_18ctnd8vuf1iui4fnqpm2tr2s.jpg', '', ''),
(11, 'Bookie', '7', '2013-12-29', 'o_18ctne8hb1alnajcs543uokfo1i.jpg', '', ''),
(12, 'Bookie', '7', '2013-12-29', 'o_18ctng5gv17lu1l9m19cd1cc11e8f1i.jpg', '', ''),
(13, 'Bookie', '9', '2014-01-07', 'o_18dn4d5cr10bg2ikg5iq71t9jq.jpg', '', ''),
(14, 'Bookie', '9', '2014-01-07', 'o_18dn50hcbdvoior1l3v10jud2qs.pdf', '', ''),
(15, 'Bookie', '9', '2014-01-16', 'o_18eeefjdnlqbl8c2v61v8oolcq.txt', '', ''),
(23, 'Blog', '3', '2014-01-16', 'o_18eeev3091jo61cod17jtt603vak.jpg', '', ''),
(24, 'Blog', '3', '2014-01-16', 'o_18eef7mdd1ie5v70tb0q6r6v5k.jpg', '', ''),
(26, 'Contacts', '12', '2014-01-17', 'o_18eguls8gqt81cb319tb6mm8s72k.jpg', '', ''),
(31, 'Contacts', '13', '2014-01-17', 'o_18eh0sk7mubp1in0a3a17b71v4ni.jpg', '', ''),
(36, 'Contacts', '18', '2014-01-24', 'o_18f30vvup1jubsi41n041org67q8.jpg', '', ''),
(37, 'Contacts', '19', '2014-01-24', 'o_18f3128k81djm17bahmvkm41irfe.jpg', '', ''),
(38, 'Contacts', '20', '2014-01-24', 'o_18f313ril122parj1ujjlj2aje8.jpg', '', ''),
(39, 'Contacts', '21', '2014-01-24', 'o_18f315cks9jq2i12f46ocfa4e.jpg', '', ''),
(40, 'Contacts', '12', '2014-02-28', 'o_18hsuqkre19q382nk5h1bi16g75.jpg', '', 'P1020456.jpg'),
(41, 'Contacts', '12', '2014-03-01', 'o_18htf5am01b5cc8n1aks19645q5a.pdf', '', 'Eng_Pacemaker_Manual_2.0.pdf'),
(42, 'Contacts', '12', '2014-03-01', 'o_18htf76ks1k1n1i1e1dcl12t616f5.pdf', '', 'diybeamer.pdf'),
(43, 'Contacts', '12', '2014-03-01', 'o_18htfcp8ar1ilu01mq3hdh18vu4.pdf', '', 'AuftragBzvPrint.pdf'),
(44, 'Bookie', '40', '2014-04-15', 'o_18ljl96d7n9bu52r6u10251vrup.png', '', 'crisscross.png');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_blog_posts`
--

CREATE TABLE IF NOT EXISTS `pp_blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `date` datetime NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Daten für Tabelle `pp_blog_posts`
--

INSERT INTO `pp_blog_posts` (`id`, `user_id`, `title`, `date`, `text`) VALUES
(2, 24, 'moin', '2014-01-05 17:35:00', 'asdf'),
(3, 24, 'test', '2014-01-05 17:40:00', '30x Situps'),
(12, 24, 'testing', '2014-01-24 22:42:31', ''),
(13, 23, 'Test', '2014-02-08 22:13:22', 'test test 123');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_blog_posts_contacts`
--

CREATE TABLE IF NOT EXISTS `pp_blog_posts_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry_id` (`entry_id`,`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `pp_blog_posts_contacts`
--

INSERT INTO `pp_blog_posts_contacts` (`id`, `entry_id`, `contact_id`) VALUES
(2, 3, 13);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_accounts`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `pp_bookie_accounts`
--

INSERT INTO `pp_bookie_accounts` (`id`, `user_id`, `name`, `notes`) VALUES
(1, -1, 'Bank', 'Geldverkehr auf Bankkonten'),
(2, -1, 'Kassa', 'Geldverkehr über Kassa bzw. Bar');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_categories`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `taxid` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Daten für Tabelle `pp_bookie_categories`
--

INSERT INTO `pp_bookie_categories` (`id`, `name`, `taxid`) VALUES
(1, 'Anschaffungen', '9130'),
(2, 'Miet und Pachtaufwand, Leasing', '9180'),
(3, 'Handelswareneinkauf', '9100'),
(4, 'Telefon/Internet', '9230'),
(5, 'Versicherungen', '9230'),
(6, 'PKW', '9170'),
(7, 'LKW', '9170'),
(8, 'Reise und Fahrtspesen - Inland', '9160'),
(9, 'Zinsen und ähnliche Aufwendungen', '9220'),
(10, 'Eigene Pflichtversicherungseiträge (Sozialaufwand, SV, Pflichtbeiträge)', '9225'),
(11, 'Werbung und Marketing', '9200'),
(12, 'Spenden und Trinkgelder', '9200'),
(13, 'Materialkauf für Weiterverarbeitung/Produktion', '9100'),
(14, 'eigenes Personal', '9120'),
(15, 'Fremdleistungen / fremdes Personal', '9110'),
(16, 'Instandhaltung von Gebäuden', '9150'),
(17, 'Provisionen an Dritte, Lizenzgebühren', '9190'),
(18, 'Sonstige Ausgaben', '9230'),
(19, 'Reise und Fahrtspesen - Ausland', '9160'),
(20, 'Anschaffung PKW', '9130'),
(21, 'Privatentnahme', '');

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
  `account_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `tax_country` int(1) NOT NULL,
  `include` int(1) NOT NULL DEFAULT '1',
  `disposal` date NOT NULL,
  `projected_disposal` date NOT NULL,
  `uid` varchar(30) NOT NULL,
  `deleted` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

--
-- Daten für Tabelle `pp_bookie_entries`
--

INSERT INTO `pp_bookie_entries` (`id`, `user_id`, `notes`, `brutto`, `netto`, `tax_type`, `tax_value`, `date`, `state`, `account_id`, `category_id`, `tax_country`, `include`, `disposal`, `projected_disposal`, `uid`, `deleted`) VALUES
(1, 24, 'Test', '100.00', '100.00', '', '0.00', '2013-12-15', 'payed', 0, 0, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(2, 24, 'Test 2', '-100.00', '-83.33', 'MwSt.', '0.20', '2013-12-15', 'delayed', 0, 0, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(4, 24, 'asdf', '4593.50', '3827.92', 'MwSt.', '0.20', '2013-12-17', 'dunned', 0, 0, 0, 0, '0000-00-00', '0000-00-00', '', 1),
(5, 24, '', '208.33', '208.33', 'MwSt.', '0.20', '2013-12-12', 'payed', 0, 0, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(6, 24, '', '333.33', '333.33', 'Ust.', '0.00', '2013-12-04', 'open', 0, 0, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(7, 24, 'so ein depp', '-200.00', '-166.67', 'Vorsteuer', '0.20', '2013-12-20', 'payed', 1, 1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(8, 24, '', '500.00', '416.67', 'MwSt.', '0.20', '2013-12-19', 'open', 0, 0, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(9, 24, '', '255.00', '212.50', '', '0.20', '2013-12-23', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(10, 24, '', '-200.00', '-181.82', 'Vorsteuer', '0.10', '2014-01-29', 'open', 2, 2, 1, 1, '0000-00-00', '0000-00-00', '', 0),
(11, 24, 'Teure Rechnung', '300.00', '250.00', 'Umsatzsteuer', '0.20', '2014-01-01', 'dunned', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(12, 24, '', '200.00', '166.67', 'Umsatzsteuer', '0.20', '2014-01-27', 'delayed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(13, 24, '', '-2000.00', '-1666.67', 'Vorsteuer', '0.20', '2014-03-13', 'open', 1, 1, 0, 0, '2014-05-14', '2018-03-13', '', 1),
(15, 24, '', '-500.00', '-416.67', 'Vorsteuer', '0.20', '2014-01-28', 'payed', 1, 2, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(16, 24, '', '-400.00', '-333.33', 'Vorsteuer', '0.20', '2014-02-13', 'open', 1, 1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(17, 24, '', '200.00', '200.00', '', '0.00', '2014-02-14', 'open', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(18, 24, '', '500.00', '416.67', 'Umsatzsteuer', '0.20', '2014-02-14', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(19, 24, '', '500.00', '416.67', 'Umsatzsteuer', '0.20', '2014-02-14', 'payed', 1, -1, 1, 1, '0000-00-00', '0000-00-00', 'ATU2345', 1),
(20, 24, '', '-500.00', '-500.00', '', '0.00', '2014-02-15', 'payed', 1, 16, 1, 1, '0000-00-00', '0000-00-00', '', 1),
(21, 24, '', '-500.00', '-416.67', 'Vorsteuer', '0.20', '2014-01-28', 'payed', 1, 4, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(22, 24, '', '-300.00', '-250.00', 'Vorsteuer', '0.20', '2014-02-15', 'payed', 2, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(23, 24, '', '500.00', '416.67', 'Umsatzsteuer', '0.20', '2014-02-15', 'payed', 2, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(24, 24, '', '-400.00', '-333.33', 'Vorsteuer', '0.20', '2014-02-15', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(25, 24, '', '-500.00', '-416.67', 'Vorsteuer', '0.20', '2014-02-15', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(26, 24, '', '-500.00', '-416.67', 'Vorsteuer', '0.20', '2014-02-15', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(27, 24, '', '-100.00', '-83.33', 'Vorsteuer', '0.20', '2014-02-15', 'payed', 1, 7, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(28, 24, '', '300.00', '300.00', '', '0.00', '2014-02-16', 'open', 1, -1, 1, 1, '0000-00-00', '0000-00-00', 'ATU12938120', 1),
(29, 24, 'EIne EU sache', '10001.00', '10001.00', '', '0.00', '2014-02-16', 'payed', 1, -1, 1, 1, '0000-00-00', '0000-00-00', 'ATU 189121', 0),
(30, 24, '', '200.00', '166.67', 'Umsatzsteuer', '0.20', '2013-12-18', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(31, 24, '', '500.00', '416.67', 'Umsatzsteuer', '0.20', '2014-02-28', 'open', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(32, 24, '', '100.00', '100.00', '', '0.00', '2014-02-28', 'open', 1, -1, 2, 1, '0000-00-00', '0000-00-00', 'ATU 2830198230', 1),
(33, 24, '', '10.00', '8.33', 'Umsatzsteuer', '0.20', '2014-02-28', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(34, 24, '', '-400.00', '-400.00', '', '0.00', '2014-03-05', 'open', 1, 19, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(35, 24, '', '-400.00', '-363.64', 'Vorsteuer', '0.10', '2014-03-05', 'open', 1, 8, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(36, 24, '', '-50000.00', '-50000.00', '', '0.00', '2014-03-05', 'payed', 1, 20, 0, 1, '0000-00-00', '2022-03-05', '', 0),
(37, 24, '', '600.00', '500.00', 'Umsatzsteuer', '0.20', '2014-04-13', 'open', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(38, 24, '', '600.00', '500.00', 'Umsatzsteuer', '0.20', '2014-04-13', 'open', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(39, 24, '', '-500.00', '-416.67', 'Vorsteuer', '0.20', '2014-04-15', 'payed', 1, 2, 1, 1, '0000-00-00', '0000-00-00', '', 0),
(40, 24, '', '-1000.00', '-833.33', 'Vorsteuer', '0.20', '2014-04-15', 'payed', 1, 1, 0, 1, '0000-00-00', '2017-04-15', '', 0),
(41, 22, '', '-500.00', '-416.67', 'Vorsteuer', '0.20', '2014-05-03', 'payed', 1, 3, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(42, 22, '', '500.00', '416.67', 'Umsatzsteuer', '0.20', '2014-08-05', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(43, 22, '', '-300.30', '-300.30', '', '0.00', '2014-08-05', 'payed', 1, 21, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(44, 22, '', '-200.00', '-200.00', '', '0.00', '2014-08-05', 'payed', 1, 21, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(45, 22, '', '-100.00', '-100.00', '', '0.00', '2014-08-05', 'payed', 1, 21, 0, 1, '0000-00-00', '0000-00-00', '', 1),
(46, 22, '', '2000.00', '1666.67', 'Umsatzsteuer', '0.20', '2014-08-20', 'open', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(47, 22, '', '1234.00', '1028.33', 'Umsatzsteuer', '0.20', '2014-08-31', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(48, 22, '', '-2342.00', '-1951.67', 'Vorsteuer', '0.20', '2014-08-31', 'payed', 1, 1, 0, 1, '0000-00-00', '2026-08-31', '', 0),
(49, 22, '', '123.00', '102.50', 'Umsatzsteuer', '0.20', '2014-08-31', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(50, 22, '', '142.00', '118.33', 'Umsatzsteuer', '0.20', '2014-08-31', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(51, 22, '', '343.00', '285.83', 'Umsatzsteuer', '0.20', '2014-08-31', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0),
(52, 22, '', '2345.00', '1954.17', 'Umsatzsteuer', '0.20', '2014-08-31', 'payed', 1, -1, 0, 1, '0000-00-00', '0000-00-00', '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_bookie_entries_contacts`
--

CREATE TABLE IF NOT EXISTS `pp_bookie_entries_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry_id` (`entry_id`,`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Daten für Tabelle `pp_bookie_entries_contacts`
--

INSERT INTO `pp_bookie_entries_contacts` (`id`, `entry_id`, `contact_id`) VALUES
(9, 7, 11),
(10, 8, 13),
(11, 9, 11),
(15, 9, 13),
(16, 10, 13),
(17, 29, 13),
(19, 33, 12);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Daten für Tabelle `pp_bookie_invoices`
--

INSERT INTO `pp_bookie_invoices` (`id`, `entry_id`, `alt_dst_adr`, `alt_src_adr`, `number`, `pay_date`, `reminder_date`, `dunnings`) VALUES
(1, 11, '', '', 'WMR_2014_000001', '2014-01-09', '0000-00-00 00:00:00', '2014-01-18,2014-01-18,2014-01-18,2014-01-18'),
(2, 12, '', '', 'WMR_2014_000002', '2014-01-27', '0000-00-00 00:00:00', ''),
(3, 28, '', '', 'WMR_2014_000003', '2014-02-16', '0000-00-00 00:00:00', ''),
(4, 29, '', '', 'WMR_2014_000004', '2014-04-17', '0000-00-00 00:00:00', ''),
(5, 31, '', '', 'WMR_2014_000005', '2014-02-28', '0000-00-00 00:00:00', ''),
(6, 37, '', '', 'WMR_2014_000006', '0000-00-00', '0000-00-00 00:00:00', ''),
(7, 38, '', '', 'WMR_2014_000007', '0000-00-00', '0000-00-00 00:00:00', ''),
(8, 46, '', '', 'WMR_2014_000001', '0000-00-00', '0000-00-00 00:00:00', '');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=52 ;

--
-- Daten für Tabelle `pp_bookie_invoice_parts`
--

INSERT INTO `pp_bookie_invoice_parts` (`id`, `invoice_id`, `notes`, `amount`, `date`) VALUES
(33, 1, 'Testing üß', '200.00', '2014-02-07'),
(34, 1, 'und so', '100.00', '2014-02-07'),
(49, 5, 'Eine Leistung', '500.00', '2014-02-28'),
(50, 4, 'hahahah', '3000.00', '2014-03-05'),
(51, 4, 'Lulz', '7001.00', '2014-03-05');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

--
-- Daten für Tabelle `pp_calendar_events`
--

INSERT INTO `pp_calendar_events` (`id`, `start_date`, `end_date`, `text`, `owner_id`) VALUES
(3, '2014-01-01 00:00:00', '2014-01-10 00:00:00', 'Neuer Termin', 24),
(4, '2014-01-01 05:40:00', '2014-01-01 10:05:00', 'Neuer Termin', 24),
(26, '2014-01-16 03:25:00', '2014-01-16 07:40:00', 'Neuer Termin asdf', 24),
(27, '2014-01-17 03:15:00', '2014-01-17 05:30:00', 'Neuer Termin', 24),
(28, '2014-01-19 01:35:00', '2014-01-19 05:10:00', 'Lauftermin', 24),
(29, '2014-01-19 06:40:00', '2014-01-19 09:00:00', 'Neuer Termin', 24),
(30, '2014-01-20 09:50:00', '2014-01-20 13:45:00', 'Mätzchen', 24),
(31, '2014-01-23 05:50:00', '2014-01-23 09:05:00', 'Neuer Termin', 24),
(32, '2014-01-25 05:10:00', '2014-01-25 08:30:00', 'Neuer Termin', 24),
(33, '2014-01-24 20:33:00', '2014-01-24 22:37:00', 'Neuer Termin jajajaja', 24),
(34, '2014-01-25 11:25:00', '2014-01-25 13:45:00', 'tadamm', 24),
(35, '2014-01-30 08:05:00', '2014-01-30 09:05:00', 'Neuer Termin', 24),
(37, '2014-01-31 06:25:00', '2014-01-31 08:05:00', 'Neuer Termin', 24),
(38, '2014-02-27 04:35:00', '2014-02-28 10:05:00', 'Neuer Termin', 24),
(40, '2014-03-01 07:00:00', '2014-03-01 10:35:00', 'Neuer Termin', 24),
(41, '2014-03-09 12:10:00', '2014-03-11 12:40:00', 'Neuer Termin', 24),
(42, '2014-03-09 15:40:00', '2014-03-09 16:10:00', 'Neuer Termin', 24),
(43, '2014-02-28 06:00:00', '2014-02-28 06:30:00', 'Neuer Termin', 23),
(45, '2014-03-21 06:05:00', '2014-03-21 10:45:00', 'Neuer Termin', 24),
(46, '2014-04-10 06:55:00', '2014-04-10 11:10:00', 'Neuer Termin', 24);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_calendar_events_contacts`
--

CREATE TABLE IF NOT EXISTS `pp_calendar_events_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry_id` (`entry_id`,`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Daten für Tabelle `pp_calendar_events_contacts`
--

INSERT INTO `pp_calendar_events_contacts` (`id`, `entry_id`, `contact_id`) VALUES
(1, 26, 13),
(2, 28, 13),
(3, 29, 8),
(4, 29, 11),
(5, 30, 11),
(6, 33, 13),
(8, 35, 13),
(11, 40, 12);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Daten für Tabelle `pp_contactdata`
--

INSERT INTO `pp_contactdata` (`id`, `contact_id`, `key`, `value`) VALUES
(1, 8, 'testfeld', 'ahahahahha'),
(6, 16, 'asdf', 'asdasd'),
(7, 18, 'Moment', ''),
(8, 18, 'Einmal', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_contacts`
--

CREATE TABLE IF NOT EXISTS `pp_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `address` varchar(200) NOT NULL,
  `pc` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `notes` text NOT NULL,
  `last_contact` datetime NOT NULL,
  `ssnum` varchar(20) NOT NULL,
  `image` varchar(200) NOT NULL,
  `birthdate` date NOT NULL,
  `company` varchar(100) NOT NULL,
  `uid` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Daten für Tabelle `pp_contacts`
--

INSERT INTO `pp_contacts` (`id`, `user_id`, `firstname`, `lastname`, `title`, `address`, `pc`, `city`, `country`, `email`, `phone`, `notes`, `last_contact`, `ssnum`, `image`, `birthdate`, `company`, `uid`) VALUES
(5, 24, 'Max', 'Muster', 'Herr', '', '', '', '', 'max@muster.com', '1234566789', '', '0000-00-00 00:00:00', '', '', '1981-07-29', '', ''),
(6, 24, 'Thomas', 'Anders', '', '', '1234', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00', '', ''),
(8, 24, 'Sabine', 'Arnautovic', '', 'Testingerstraße 23', '1234', 'Wien', '', 'sabine.aranautovic.mayerhuber@gmail.com', '', 'asdf', '0000-00-00 00:00:00', '', 'o_18cbjracp1kghe2915131h0r157ee.jpg', '2014-01-17', '', ''),
(9, 24, 'Thomas', 'Breuer', '', '', '', '', '', '', '0000 000 0', '', '0000-00-00 00:00:00', '', '', '0000-00-00', '', ''),
(11, 24, 'Kevin', 'Tran', '', '', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00', '', ''),
(12, 24, 'Thomas', 'Albrecht', 'Dr. Dr. Mag.', 'Teststraße 1', '1234', 'Testing', 'Tastistan', 'thomas.albrecht@testing.com', '1234556678', '', '0000-00-00 00:00:00', '', 'o_18cvds99g1eqd112e1rm31m7c1hp48.jpg', '2013-12-29', 'Testing Inc.', 'ATU 123456789'),
(13, 24, 'Maxi', 'Müller', 'Dr. Mag.', '', '1234', 'Wien', '', '', '', '', '0000-00-00 00:00:00', '', 'o_18cbk05ml1v9137o1aa51h1mqn10.jpg', '2014-02-28', '', 'ATU 237817381'),
(14, 24, 'Maxi', 'Muster', '', '', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00', '', ''),
(16, 24, 'Maxi', 'Müllersen', '', '', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00', '', ''),
(17, 24, 'Tom', 'Master', 'Herr', '', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '2014-03-05', '', ''),
(18, 24, 'Of', 'Zeppelin', 'Master', '', '', '', '', '', '', '', '0000-00-00 00:00:00', '', '', '0000-00-00', '', '');

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
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `webname` varchar(100) NOT NULL,
  `scope` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61 ;

--
-- Daten für Tabelle `pp_tags`
--

INSERT INTO `pp_tags` (`id`, `user_id`, `name`, `webname`, `scope`) VALUES
(1, -1, 'Laufen', 'laufen', ':Blog:Calendar:Bookie:'),
(3, 24, 'Springen', 'springen', ':Blog:Calendar:Bookie:'),
(4, 24, 'Bauch', 'bauch', ':Blog:'),
(5, 24, 'Benzin', 'benzin', ':Bookie:Blog:'),
(27, 24, 'Joggen', 'joggen', ':Calendar:'),
(28, 24, 'Crunching', 'crunching', ':Calendar:'),
(29, 24, '[frontpage]', '[frontpage]', ':Blog:'),
(57, 24, 'Jumping', 'jumping', ':Calendar:'),
(58, 23, 'Springen', 'springen', ':Blog:'),
(59, 24, 'EU', 'eu', ':Bookie:'),
(60, 24, 'lolercoaster', 'lolercoaster', ':Bookie:');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pp_tag_links`
--

CREATE TABLE IF NOT EXISTS `pp_tag_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `service` varchar(100) NOT NULL,
  `param` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_id` (`tag_id`,`service`,`param`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=129 ;

--
-- Daten für Tabelle `pp_tag_links`
--

INSERT INTO `pp_tag_links` (`id`, `tag_id`, `service`, `param`) VALUES
(6, 1, 'Blog', '3'),
(58, 1, 'Calendar', '26'),
(86, 1, 'Calendar', '32'),
(124, 1, 'Calendar', '37'),
(20, 3, 'Blog', '2'),
(9, 3, 'Blog', '3'),
(74, 3, 'Calendar', '30'),
(105, 3, 'Calendar', '31'),
(121, 3, 'Calendar', '35'),
(125, 3, 'Calendar', '37'),
(47, 5, 'Blog', '3'),
(21, 5, 'Bookie', '10'),
(65, 22, 'Bookie', '11'),
(66, 23, 'Bookie', '9'),
(69, 26, 'Calendar', '29'),
(70, 27, 'Calendar', '28'),
(71, 28, 'Calendar', '28'),
(76, 29, 'Blog', '2'),
(80, 29, 'Blog', '3'),
(126, 58, 'Blog', '13'),
(127, 59, 'Bookie', '29'),
(128, 60, 'Bookie', '29');

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
(1, 'root', 'a3597971769fc171e38fb92ff3cd4cc429370b618342836ff7a2eb61fe7d6f70ead7dd6586c2044d759ab962b6fbb96d48981259e592e3c79b559d84a79fe64a#me:fpeH2cc68;p9npeQ/Qemi0UQ%Wu!g4Hweu=US4JsPUxqa-Oe', 1, 'root@apple.com', '', '', 1, 0, 1407269299),
(24, 'tester', 'c708109949e40eb065844fad07f90e45114c1f3a5beb06b5a451855894b9f7a8aa7a72903e761d67ec9ea30269abeef15989dd4ce931d8f853a0468c12be154c#d3x,Zgv2F|;dntc5Zq_YUuucom!|R6tB5j5dj$VxmUaj8fNhAYG', 3, 'lol@test.com', '', '', 1, 1385307568, 1409511557),
(23, 'tester1', '671ccdec908af53b2701e800c40695fff0db7970495fa13448fb2aecb7cfefe15feb100679ea1d559ff08ae707c96fb4c36f7af12204a3d24acbc69390456778#yTlji4B7C\\!klsnm4-J3i\\X14iDsuXiA$3.NkmL$fLLddutwoZf', 3, 'lol@test.com', '', '', 1, 1385307568, 1409511576),
(22, 'tester2', 'a929adf5e33f76c17fbc1084b26e6ba234804b40808ba33f78c08aea044c38ee2b77ed0ed2978d3e85a479067d22a1454c8e66ca5488331b7458ab4bdd1b97a7#XjUOu@6jWEKf$ci$Ydxp:V3o52ah5&tN5hmNikzk%?q5pFeWhIg', 1, 'lol@test.com', '', '', 1, 1385307567, 1409511584),
(21, 'tester3', 'e34f95d898907dc4a1bebd377679ea945b3854c3879fbc0df459551690cfb4017419f665ad79b6aae3f207771962592e18ac2e24a72f78d0650c5f220b1f64f6#KwnsI1fdYQk2M5u2\\9lBlXwHzmo5|xG?,+mExwudiU%dcsh7PY$', 1, 'lol@test.com', '', '', 1, 1385307566, 1409511569),
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=62 ;

--
-- Daten für Tabelle `pp_userdata`
--

INSERT INTO `pp_userdata` (`id`, `user_id`, `field_id`, `value`, `last_change`) VALUES
(1, 19, 1, 'Tester', '2013-11-30 21:53:26'),
(2, 19, 2, 'McTestinger', '2013-11-30 21:53:26'),
(3, 20, 1, '', '2013-11-30 21:53:54'),
(4, 20, 2, '', '2013-11-30 21:53:54'),
(5, 24, 1, 'Tester', '2014-05-04 22:07:46'),
(6, 24, 2, 'McTest', '2014-05-04 22:07:46'),
(7, 24, 3, 'lol@test.com', '2014-05-04 22:08:06'),
(8, 24, 4, 'agenda', '2014-05-04 22:08:06'),
(9, 24, 6, 'Thomas Muster\r\nFakestreet 123\r\nTesttown 1234', '2014-05-04 22:08:06'),
(10, 24, 7, '1234', '2014-05-04 22:08:06'),
(11, 24, 8, '5678', '2014-05-04 22:08:06'),
(12, 24, 9, '1', '2014-05-04 22:08:06'),
(13, 24, 10, '14', '2014-05-04 22:08:06'),
(14, 24, 5, '0', '2014-05-04 22:08:06'),
(15, 24, 11, 'WMR_', '2014-05-04 22:07:46'),
(16, 24, 12, '4', '2014-05-04 22:08:06'),
(17, 24, 13, '30', '2014-05-04 22:08:06'),
(19, 23, 3, 'lol@test.com', '2014-02-09 21:53:55'),
(20, 23, 4, 'month', '2014-02-09 21:53:55'),
(21, 23, 12, '6', '2014-02-09 21:53:55'),
(22, 23, 13, '30', '2014-02-09 21:53:55'),
(23, 23, 5, '0', '2014-02-09 21:53:55'),
(24, 23, 11, 'WMR_', '2014-02-09 21:53:55'),
(25, 23, 6, '', '2014-02-09 21:53:55'),
(26, 23, 7, '123456', '2014-02-09 21:53:55'),
(27, 23, 8, '', '2014-02-09 21:53:55'),
(28, 23, 9, '1', '2014-02-09 21:53:55'),
(29, 23, 10, '14', '2014-02-09 21:53:55'),
(30, 21, 3, 'lol@test.com', '2014-03-23 01:23:14'),
(31, 21, 4, 'month', '2014-03-23 01:23:14'),
(32, 21, 12, '6', '2014-03-23 01:23:14'),
(33, 21, 13, '30', '2014-03-23 01:23:14'),
(34, 21, 5, '1', '2014-03-23 01:23:14'),
(35, 21, 11, 'WMR_', '2014-03-23 01:23:14'),
(36, 21, 6, '', '2014-03-23 01:23:14'),
(37, 21, 7, '', '2014-03-23 01:23:14'),
(38, 21, 8, '', '2014-03-23 01:23:14'),
(39, 21, 9, '1', '2014-03-23 01:23:14'),
(40, 21, 10, '14', '2014-03-23 01:23:14'),
(41, 24, 14, 'http://wingmen.at/img/logo@2x.png', '2014-05-04 22:08:06'),
(42, 24, 16, '', '2014-05-04 22:08:06'),
(43, 24, 15, '', '2014-05-04 22:08:06'),
(44, 24, 17, '0', '2014-05-04 22:08:06'),
(45, 22, 3, 'lol@test.com', '2014-08-05 22:08:30'),
(46, 22, 17, '1', '2014-08-05 22:08:30'),
(47, 22, 4, 'month', '2014-08-05 22:08:30'),
(48, 22, 12, '6', '2014-08-05 22:08:30'),
(49, 22, 13, '30', '2014-08-05 22:08:30'),
(50, 22, 5, '1', '2014-08-05 22:08:30'),
(51, 22, 11, 'WMR_', '2014-08-05 22:08:30'),
(52, 22, 14, '', '2014-08-05 22:08:30'),
(53, 22, 6, '', '2014-08-05 22:08:30'),
(54, 22, 16, '', '2014-08-05 22:08:30'),
(55, 22, 15, 'ATU123102', '2014-08-05 22:08:30'),
(56, 22, 7, '', '2014-08-05 22:08:30'),
(57, 22, 8, '', '2014-08-05 22:08:30'),
(58, 22, 9, '1', '2014-08-05 22:08:30'),
(59, 22, 10, '14', '2014-08-05 22:08:30'),
(60, 22, 1, '', '2014-08-05 22:08:30'),
(61, 22, 2, '', '2014-08-05 22:08:30');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Daten für Tabelle `pp_userdatafield`
--

INSERT INTO `pp_userdatafield` (`id`, `name`, `info`, `type`, `group`, `vis_register`, `vis_login`, `vis_edit`) VALUES
(1, 'vname', 'Vorname', 1, 0, 1, 0, 1),
(2, 'nname', 'Nachname', 1, 0, 1, 0, 1),
(3, 'set.contact_email', 'Kontakt Email', 1, 0, 0, 0, 1),
(4, 'set.calendar_start_view', 'Kalender Startansicht', 0, 0, 0, 0, 1),
(5, 'set.taxes', 'Umsatzsteuer', 2, 0, 0, 0, 1),
(6, 'set.address', 'Rechnungsadresse', 6, 0, 0, 0, 1),
(7, 'set.iban', 'IBAN', 1, 0, 0, 0, 1),
(8, 'set.bic', 'BIC', 1, 0, 0, 0, 1),
(9, 'set.default_account', 'Standard Geldfluss', 0, 0, 0, 0, 1),
(10, 'set.dunning_interval', 'Mahnungsintervall', 0, 0, 0, 0, 1),
(11, 'set.invoice_prefix', 'Rechnungsnummer Präfix', 1, 0, 0, 0, 1),
(12, 'set.first_hour', 'Termine ab Uhrzeit', 0, 0, 0, 0, 1),
(13, 'set.event_duration', 'Standard Calendar Event length', 0, 0, 0, 0, 1),
(14, 'set.invoice_logo_url', 'Invoice Logo Url', 1, 0, 0, 0, 1),
(15, 'set.uid', 'Umsatzsteueridentifikationsnummer', 1, 0, 0, 0, 1),
(16, 'set.invoice_footer', 'Rechnnung Fußzeile', 6, 0, 0, 0, 1),
(17, 'set.has_bookie', 'Finanz Modul', 2, 0, 0, 0, 1);

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
