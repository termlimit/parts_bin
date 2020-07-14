-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 20, 2019 at 03:05 PM
-- Server version: 10.0.38-MariaDB
-- PHP Version: 7.3.6

--
-- Open source version of MyPartsBin.com by The Defpom
-- Please visit http://www.TheDefpom.com and http://www.MyPartsBin.com
--
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myparts_db1`
--
-- CREATE DATABASE IF NOT EXISTS `parts_bin` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
-- USE `parts_bin`;

-- --------------------------------------------------------

--
-- Table structure for table `errorlog`
--
CREATE TABLE `errorlog` (
  `pagename` varchar(256) NOT NULL,
  `errormessage` varchar(1024) NOT NULL,
  `errorsql` varchar(1024) NOT NULL,
  `datetime` int(15) NOT NULL,
  `spare` varchar(64) NOT NULL,
  `ip` varchar(128) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `devices_inventory`
--
CREATE TABLE `devices_inventory` (
  `deviceid` int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `devicenumber` varchar(128) NOT NULL,
  `devicepackage` varchar(32) NOT NULL,
  `devicetype` varchar(32) NOT NULL,
  `devicedescription` varchar(256) NOT NULL,
  `devicequantity` varchar(16) NOT NULL,
  `devicepackaging` varchar(32) NOT NULL,
  `devicebinlocation` varchar(32) NOT NULL,
  `devicelink` varchar(256) NOT NULL,
  `project` varchar(64) NOT NULL,
  `notes` MEDIUMTEXT NOT NULL,
  `listprice` decimal(20,2) NOT NULL,
  `currentprice` decimal(20,2) NOT NULL,
  `pricedate` datetime NOT NULL,
  `datetime` int(16) NOT NULL,
  `userid` int(11) DEFAULT NULL,
  `spare` varchar(64) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
create table `users` (
  id int(10) unsigned NOT NULL auto_increment,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  is_superuser int(1) NOT NULL DEFAULT '0',
  user_pin INT(4) DEFAULT '0' NOT NULL,
  email varchar(50) DEFAULT '0' NOT NULL,
  parent_email varchar(50) NOT NULL,
  join_date int(10) unsigned DEFAULT '0' NOT NULL,
  last_visit int(10) unsigned DEFAULT '0' NOT NULL,
  last_activity int(10) unsigned DEFAULT '0' NOT NULL,
  last_post int(10) unsigned DEFAULT '0' NOT NULL,
  posts smallint(5) unsigned DEFAULT '0' NOT NULL,
  email_notification smallint(6) DEFAULT '0' NOT NULL,
  token varchar(255) DEFAULT '' NOT NULL,
  token_expires datetime DEFAULT NULL,
  api_token varchar(255) DEFAULT NULL,
  activation_date datetime DEFAULT NULL,
  active int(1) NOT NULL DEFAULT '0',
  created datetime NOT NULL,
  modified datetime NOT NULL,
  verified tinyint(1) DEFAULT 0 NOT NULL,
  activated smallint(5) UNSIGNED DEFAULT 0 NOT NULL,
  PRIMARY KEY (id),
  KEY username (username)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `errorlog`
--
ALTER TABLE `errorlog`
  ADD UNIQUE KEY `datetime` (`datetime`),
  ADD KEY `pagename` (`pagename`);

--
-- Indexes for table `devices_inventory`
--
ALTER TABLE `devices_inventory`
  ADD KEY `devicenumber` (`devicenumber`),
  ADD KEY `devicename` (`devicetype`),
  ADD KEY `devicedescription` (`devicedescription`),
  ADD KEY `devicebinlocation` (`devicebinlocation`);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
