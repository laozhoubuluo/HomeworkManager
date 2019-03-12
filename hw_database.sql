-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 建立日期: Aug 30, 2012, 01:38 PM
-- 伺服器版本: 5.0.95
-- PHP 版本: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 资料库: `warehouse`
--

-- --------------------------------------------------------

--
-- 资料表格式： `hwList`
--

CREATE TABLE IF NOT EXISTS `hwList` (
  `hID` int(11) NOT NULL auto_increment,
  `hwTitle` varchar(100) collate utf8mb4_unicode_ci NOT NULL,
  `hwO` varchar(20) collate utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) collate utf8mb4_unicode_ci NOT NULL,
  `classID` varchar(12) collate utf8mb4_unicode_ci NOT NULL,
  `remark` varchar(1000) collate utf8mb4_unicode_ci NOT NULL,
  `passwd` varchar(20) collate utf8mb4_unicode_ci NOT NULL,
  `fromDT` datetime NOT NULL,
  `dueDT` datetime NOT NULL,
  `display` tinyint(4) NOT NULL default '1',
  `closed` tinyint(4) NOT NULL default '0',
  `upPasswd` tinyint(4) NOT NULL default '0',
  `rank` smallint(6) NOT NULL default '20',
  `clicked` int(11) NOT NULL,
  `uploadCnt` mediumint(9) NOT NULL default '0',
  `passedCnt` mediumint(9) NOT NULL default '0',
  `lastModDT` timestamp NULL default NULL,
  `uDT` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `cDT` datetime NOT NULL,
  PRIMARY KEY  (`hID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- 资料表格式： `hwUpload`
--

CREATE TABLE IF NOT EXISTS `hwUpload` (
  `sn` int(11) NOT NULL auto_increment,
  `hID` int(11) NOT NULL,
  `cid` varchar(20) collate utf8mb4_unicode_ci NOT NULL,
  `cname` varchar(20) collate utf8mb4_unicode_ci NOT NULL,
  `modPasswd` varchar(20) collate utf8mb4_unicode_ci default NULL,
  `fileName` varchar(100) collate utf8mb4_unicode_ci NOT NULL,
  `oFileName` varchar(100) collate utf8mb4_unicode_ci NOT NULL,
  `remark` varchar(200) collate utf8mb4_unicode_ci NOT NULL,
  `size` int(11) NOT NULL,
  `ext` varchar(10) collate utf8mb4_unicode_ci NOT NULL,
  `passed` tinyint(4) NOT NULL default '0',
  `score` varchar(12) collate utf8mb4_unicode_ci NOT NULL default '0',
  `display` tinyint(4) NOT NULL default '0',
  `uDT` datetime NOT NULL,
  `cDT` datetime NOT NULL,
  PRIMARY KEY  (`sn`),
  KEY `hID` (`hID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- 资料表格式： `member`
--

CREATE TABLE IF NOT EXISTS `member` (
  `email` varchar(60) NOT NULL default '',
  `usID` varchar(24) default NULL,
  `comID` mediumint(9) NOT NULL default '0',
  `cname` varchar(16) default NULL,
  `exp` int(11) NOT NULL default '0',
  `shadow` varchar(48) NOT NULL,
  `passwd` varchar(48) NOT NULL,
  `sex` tinyint(1) NOT NULL default '-1',
  `birthday` date NOT NULL default '0000-00-00',
  `visitCount` smallint(5) unsigned NOT NULL default '0',
  `lastVisit` datetime default NULL,
  `tel` varchar(20) default NULL,
  `cellphone` varchar(20) default NULL,
  `cmmuAddr` varchar(64) default NULL COMMENT 'e€?e‥?a?°a?€',
  `cmmuZcode` varchar(5) default NULL COMMENT 'e€?e‥?a?°a?€ZIPcode',
  `from` varchar(32) NOT NULL,
  `signature` varchar(255) NOT NULL,
  `regDT` datetime NOT NULL COMMENT 'a﹐3a??e‥?a??a??e–“',
  `role` varchar(64) NOT NULL default '2',
  PRIMARY KEY  (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- 列出以下资料库的数据： `member`
--

INSERT INTO `member` (`email`, `usID`, `comID`, `cname`, `exp`, `shadow`, `passwd`, `sex`, `birthday`, `visitCount`, `lastVisit`, `tel`, `cellphone`, `cmmuAddr`, `cmmuZcode`, `from`, `signature`, `regDT`, `role`) VALUES
('default@note.tc.edu.tw', 'default', 0, 'default', 0, 'efd43bdd657de57f01d28233f80c1218', '4a7d1ed414474e4033ac29ccb8653d9b', -1, '0000-00-00', 0, NULL, NULL, NULL, NULL, NULL, '', '', '0000-00-00 00:00:00', '2');

-- --------------------------------------------------------

--
-- 资料表格式： `reclogin`
--

CREATE TABLE IF NOT EXISTS `reclogin` (
  `lID` int(11) NOT NULL auto_increment,
  `recDT` datetime NOT NULL,
  `email` varchar(60) collate utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(40) collate utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) collate utf8mb4_unicode_ci NOT NULL,
  `desc` varchar(100) collate utf8mb4_unicode_ci default NULL,
  PRIMARY KEY  (`lID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- 资料表格式： `recmng`
--

CREATE TABLE IF NOT EXISTS `recmng` (
  `mID` int(11) NOT NULL auto_increment,
  `recDT` datetime NOT NULL,
  `email` varchar(60) collate utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(40) collate utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) collate utf8mb4_unicode_ci NOT NULL,
  `desc` varchar(100) collate utf8mb4_unicode_ci default NULL,
  PRIMARY KEY  (`mID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

--
-- 列出以下资料库的数据： `recmng`
--


--
-- 资料表格式： `recuser`
--

CREATE TABLE IF NOT EXISTS `recuser` (
  `uID` int(11) NOT NULL auto_increment,
  `recDT` datetime NOT NULL,
  `email` varchar(60) collate utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(40) collate utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) collate utf8mb4_unicode_ci NOT NULL,
  `desc` varchar(100) collate utf8mb4_unicode_ci default NULL,
  PRIMARY KEY  (`uID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;


-- 资料表限制 `hwUpload`
--
ALTER TABLE `hwUpload`
  ADD CONSTRAINT `hwUpload_ibfk_1` FOREIGN KEY (`hID`) REFERENCES `hwList` (`hID`) ON DELETE CASCADE ON UPDATE CASCADE;
