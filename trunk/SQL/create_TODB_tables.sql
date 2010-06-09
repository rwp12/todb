-- MySQL dump 10.10
--
-- Host: localhost    Database: todb_1_2g
-- ------------------------------------------------------
-- Server version	5.0.18-standard

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `config_years`
--

DROP TABLE IF EXISTS `config_years`;
CREATE TABLE `config_years` (
  `id` int(11) NOT NULL auto_increment,
  `yearval` varchar(32) default NULL,
  `description` varchar(32) default NULL,
  `notinflux` int(1) default '1',
  `yearorder` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `config_years`
--


/*!40000 ALTER TABLE `config_years` DISABLE KEYS */;
LOCK TABLES `config_years` WRITE;
INSERT INTO `config_years` VALUES (15,'2009_10','Oct 2009 - 2010',1,NULL),(16,'2009_10','current_year',1,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `config_years` ENABLE KEYS */;


--
-- Table structure for table `editlocks_2009_10`
--

DROP TABLE IF EXISTS `editlocks_2009_10`;
CREATE TABLE `editlocks_2009_10` (
  `id` int(11) NOT NULL auto_increment,
  `dbname` varchar(12) NOT NULL,
  `inuse` tinyint(1) NOT NULL default '0',
  `currentuser` varchar(32) default NULL,
  `lastuser` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `dbname` (`dbname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `editlocks_2009_10`
--


/*!40000 ALTER TABLE `editlocks_2009_10` DISABLE KEYS */;
LOCK TABLES `editlocks_2009_10` WRITE;
INSERT INTO `editlocks_2009_10` VALUES (1,'people',0,NULL,NULL),(2,'jobs',0,NULL,NULL),(3,'units',0,NULL,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `editlocks_2009_10` ENABLE KEYS */;

--
-- Table structure for table `jobs_2009_10`
--

DROP TABLE IF EXISTS `jobs_2009_10`;
CREATE TABLE `jobs_2009_10` (
  `id` int(11) NOT NULL auto_increment,
  `course` varchar(32) default NULL COMMENT 'Course to which this job belongs',
  `year` int(11) default NULL COMMENT 'Year  number in course to which this job belongs',
  `paper` varchar(32) default NULL COMMENT 'Teaching unit/Module/Paper to which this job belongs',
  `prgroup` varchar(32) default NULL COMMENT 'Subject groups to which this job belongs (comma-separated list)',
  `name` text COMMENT 'The name/description of the job',
  `type` varchar(32) default NULL COMMENT 'The type of work that this job entails - lecturing (L), class (C), admin (A), etc',
  `hours` decimal(5,1) default NULL COMMENT 'Number of hours allocated to this job',
  `term` varchar(32) default NULL COMMENT 'Term in which this job is done',
  `venue` varchar(64) default NULL COMMENT 'Venue where this lecture/class/seminar is given (if appropriate)',
  `timeslots` varchar(64) default NULL COMMENT 'eg Wk1 M12, W4, Wk2 Th3 for a series over a couple of weeks. Week, Day and Time of day where this job is given. A Lecture in Week 2 on Tuesday at 11 would be Wk2 Tu. 11. A space- or comma-separated sequence is acceptable',
  `uname` varchar(32) default NULL COMMENT 'Unique name of the person doing this job',
  `points` int(11) default NULL COMMENT 'Number of stint points earned by doing this job',
  `note` text COMMENT 'Extra information and notes',
  `deleted` tinyint(1) NOT NULL default '0' COMMENT 'Flags this record as deleted or not',
  `updatetime` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When this job was added or last edited',
  `formula_ref` int(11) default NULL COMMENT 'Reference to a formula for calculating points based on number of students registered for a paper/unit',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jobs_2009_10`
--


/*!40000 ALTER TABLE `jobs_2009_10` DISABLE KEYS */;
LOCK TABLES `jobs_2009_10` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `jobs_2009_10` ENABLE KEYS */;

--
-- Table structure for table `people_2009_10`
--

DROP TABLE IF EXISTS `people_2009_10`;
CREATE TABLE `people_2009_10` (
  `id` int(11) NOT NULL auto_increment,
  `uname` varchar(64) NOT NULL COMMENT 'Unique name of this person',
  `crsid` varchar(32) default NULL COMMENT 'Cambridge CRSID of this person',
  `engid` varchar(32) default NULL COMMENT 'Only used in Engineering',
  `division` varchar(32) default NULL COMMENT 'Subject group divisions to which this person belongs; separate by commas if the person belongs to several groups',
  `title` varchar(20) default NULL COMMENT 'Prof, Dr, Mr, Mrs etc',
  `called` varchar(32) default NULL COMMENT 'First name',
  `surname` varchar(64) default NULL COMMENT 'Person`s surname',
  `initials` varchar(32) default NULL COMMENT "Person`s initials",
  `quota` int(11) NOT NULL default '70' COMMENT 'Any target number of points',
  `room` text COMMENT "Person`s room number",
  `PostalAddress` text,
  `phone` varchar(32) default NULL COMMENT "Person`s phone number",
  `mobile` varchar(32) default NULL COMMENT 'Mobile phone number',
  `homephone` varchar(32) default NULL COMMENT 'Home phone number',
  `ice` varchar(32) default NULL COMMENT 'Number  to call in case of emergency',
  `job_title` varchar(32) default NULL COMMENT "Person`s job title",
  `college` varchar(4) default NULL COMMENT "Person`s college",
  `status` varchar(32) default NULL COMMENT 'Affiliation status: affiliated, federated, etc',
  `deleted` tinyint(1) NOT NULL default '0',
  `OK06` datetime default NULL COMMENT 'The date on which the teaching duties for this person were confirmed',
  `updatetime` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When this person was added or last edited',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uname` (`uname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `people_2009_10`
--


/*!40000 ALTER TABLE `people_2009_10` DISABLE KEYS */;
LOCK TABLES `people_2009_10` WRITE;
UNLOCK tables;
/*!40000 ALTER TABLE `people_2009_10` ENABLE KEYS */;

--
-- Table structure for table `point_formulae_2009_10`
--

DROP TABLE IF EXISTS `point_formulae_2009_10`;
CREATE TABLE `point_formulae_2009_10` (
  `Formula_ID` int(11) NOT NULL auto_increment,
  `F_Name` char(255) NOT NULL,
  `F_Math_Desc` char(50) default NULL,
  `n_Multiplier` float NOT NULL default '0',
  `offset` float NOT NULL default '0',
  PRIMARY KEY  (`Formula_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `point_formulae_2009_10`
--


/*!40000 ALTER TABLE `point_formulae_2009_10` DISABLE KEYS */;
LOCK TABLES `point_formulae_2009_10` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `point_formulae_2009_10` ENABLE KEYS */;

--
-- Table structure for table `studentspercourse_2009_10`
--

DROP TABLE IF EXISTS `studentspercourse_2009_10`;
CREATE TABLE `studentspercourse_2009_10` (
  `ID` int(11) NOT NULL auto_increment,
  `coursename` varchar(10) NOT NULL,
  `student_count` int(11) default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `coursename` (`coursename`),
  UNIQUE KEY `coursename_2` (`coursename`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `studentspercourse_2009_10`
--


/*!40000 ALTER TABLE `studentspercourse_2009_10` DISABLE KEYS */;
LOCK TABLES `studentspercourse_2009_10` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `studentspercourse_2009_10` ENABLE KEYS */;

--
-- Table structure for table `units_2009_10`
--

DROP TABLE IF EXISTS `units_2009_10`;
CREATE TABLE `units_2009_10` (
  `id` int(11) NOT NULL auto_increment,
  `uname` varchar(32) default NULL COMMENT 'Unique name of this unit/paper',
  `course` varchar(32) default NULL COMMENT 'Course of which this unit is a part',
  `ordering` int(11) default NULL COMMENT 'If a special order of units is desired, a number can be entered here.  A low number will put it near the top of the list and a high number lower down the list' ,
  `sgrps` varchar(32) default NULL COMMENT 'Subject groups to which this unit is associated (comma-separated list)',
  `name` text COMMENT 'Name/description of this unit',
  `assessmode` varchar(32) default NULL COMMENT 'Assessment Mode',
  `running` tinyint(1) default '1' COMMENT 'Tick indicates that this unit is running in this academic year',
  `note` text COMMENT 'Extra information and notes',
  `deleted` tinyint(1) default '0',
  `global` tinyint(1) default '0' COMMENT 'units that are interesting to everyone',
  `updatetime` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When this unit information was added or last edited',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unit` (`uname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `units_2009_10`
--


/*!40000 ALTER TABLE `units_2009_10` DISABLE KEYS */;
LOCK TABLES `units_2009_10` WRITE;

UNLOCK TABLES;
/*!40000 ALTER TABLE `units_2009_10` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

