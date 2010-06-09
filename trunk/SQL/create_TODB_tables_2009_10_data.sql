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
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1CW','2','Experiment Leader','P','4','M','Lab 1','wk1 M.14, W.14',NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1CW','2','Experiment Leader','P','4','L','Lab 1','wk1 Tu.14, W.14',NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1CW','2','Lab Demonstrating','P','4','M','Lab 1','Wk2 M.14, W.14',NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1CW','2','Lab Demonstrating','P','4','L','Lab 1','Wk2 Tu.14, W.14',NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1CW','2','Lab Co-ordinator', 'A','2','L',NULL,NULL,NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1CW','2','Lab Co-ordinator', 'A','2','M',NULL,NULL,NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1CW','2','Lab Co-ordinator', 'A','2','E',NULL,NULL,NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','3','Assessor','E','10','E',NULL,NULL,NULL,1000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','3','Examiner','E','10','E',NULL,NULL,NULL,1200,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','1','Lecture 1','L','1','M','Theatre 1','Wk1 M.10, W.10, F.10',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','2','Lecture 2','L','1','M','Theatre 3','Wk2 M.10, W.10, F.10',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','3','Lecture 3','L','1','M','Theatre 5','Wk1 M.10, W.10, F.10',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','1','Lecture 1','L','1','L','Theatre 1','Wk4 M.10, Wk5 W.10, F.10',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','2','Lecture 2','L','1','L','Theatre 3','Wk1 M.10, W.10, F.10',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P1','3','Lecture 3','L','1','L','Theatre 5','Wk3 M.10, W.10, F.10',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P2','1','Lecture 1','L','1','M','Theatre 1','Wk5 M.11, W.11, F.11',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P2','2','Lecture 2','L','1','M','Theatre 3','Wk6 M.11, W.11, F.11',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P2','3','Lecture 3','L','1','M','Theatre 5','Wk1 M.11, W.11, F.11',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P2','1','Lecture 1','L','1','L','Theatre 1','Wk3 M.11, W.11, F.11',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P2','2','Lecture 2','L','1','L','Theatre 3','Wk2 M.11, W.11, F.11',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',1,'1P2','3','Lecture 3','L','1','L','Theatre 5','Wk1 M.11, W.11, F.11',NULL,600,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Phd',NULL,NULL,NULL,'Supervision:1',NULL,NULL,NULL,NULL,NULL,NULL,4000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Phd',NULL,NULL,NULL,'Supervision:2',NULL,NULL,NULL,NULL,NULL,NULL,4000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Phd',NULL,NULL,NULL,'Supervision:3',NULL,NULL,NULL,NULL,NULL,NULL,4000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Phd',NULL,NULL,NULL,'Supervision:4',NULL,NULL,NULL,NULL,NULL,NULL,4000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Phd',NULL,NULL,NULL,'Supervision:7',NULL,NULL,NULL,NULL,NULL,NULL,4000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Phd',NULL,NULL,NULL,'Supervision:5',NULL,NULL,NULL,NULL,NULL,NULL,4000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Phd',NULL,NULL,NULL,'Supervision:6',NULL,NULL,NULL,NULL,NULL,NULL,6000,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',4,'4P2','1','Seminar 1','S','2','L','Room 2','Wk1 M.12, Wk 3 M.12',NULL,400,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',4,'4P3','3','Seminar 2','S','2','M','Room 2','Wk1 M.12, Wk 3 M.12',NULL,400,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',4,'4P4','4','Seminar 3','S','2','E','Room 2','Wk1 M.12, Wk 3 M.12',NULL,400,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',4,'4P2','1','Seminar 4','S','2','L','Room 2','Wk1 M.12, Wk 3 M.12',NULL,400,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',4,'4P3','3','Seminar 5','S','2','M','Room 2','Wk1 M.12, Wk 3 M.12',NULL,400,NULL,0,'2009-06-17 14:26:04',NULL);
INSERT INTO `jobs_2009_10` VALUES (NULL,'Undergrad',4,'4P2','1','Seminar 6','S','2','E','Room 2','Wk1 M.12, Wk 3 M.12',NULL,400,NULL,0,'2009-06-17 14:26:04',NULL);




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
  `engid` varchar(32) default NULL COMMENT 'Only used in Engineering' ,
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
INSERT INTO `people_2009_10` VALUES (1,'Abbott_C','ab1',NULL,'1','Mrs','Tina','Abbott','C',100, 'Rm1','10 High St','123456','07999 123456', NULL, NULL,'Lecturer','N','Affiliated',0,'2009-06-16 13:42:41',NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Able_A','ab2',NULL,'2','Mr','Andy','Able','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Able_M','ab3',NULL,'2','Mr','Mick','Able','M',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Brown','br1',NULL,'3','Mr',NULL,'Brown','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Cush','Cu1',NULL,'2','Mr',NULL,'Cush','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Downing','Do3',NULL,'2','Mr',NULL,'Downing','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Angel','an3',NULL,'3','Dr',NULL,'Angel','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Black','bl3',NULL,'2','Dr',NULL,'Black','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Amble','am5',NULL,'2','Dr',NULL,'Amble','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Zebra','z1',NULL,'3','Dr',NULL,'Zebra','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Lecturer',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Anwar','an6',NULL,'2','Prof',NULL,'Anwar','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Browning','br4',NULL,'2','Dr',NULL,'Browning','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Brownson','br5',NULL,'2','Dr',NULL,'Brownson','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Bell','be7',NULL,'2','Dr',NULL,'Bell','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Belling','be8',NULL,'5','Dr',NULL,'Belling','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Bellis','be2',NULL,'6','Dr',NULL,'Bellis','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Demonstrator',NULL,NULL,0,NULL, NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'Ainger_A','ai8',NULL,'1','Mrs','Avril','Ainger','A',100, 'Rm1','14 High St','123456','07999 123456', NULL, NULL,'Lecturer','N','Affiliated',0,'2009-06-16 13:42:41',NULL);
INSERT INTO `people_2009_10` VALUES (NULL,'General_N','gen1',NULL,'1','Dr',NULL,'General','A',70,NULL,NULL,NULL,NULL, NULL, NULL,'Lecturer',NULL,NULL,0,NULL, NULL);


UNLOCK TABLES;
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
INSERT INTO `point_formulae_2009_10` VALUES (NULL,'Project Supervision (40xn)','40 x n',40,0);
INSERT INTO `point_formulae_2009_10` VALUES (NULL,'Principal Assessor (15+n)','15 + (n)',1,15);
INSERT INTO `point_formulae_2009_10` VALUES (NULL,'Lab Leader (24+n)','24 + (n)',1,24);
INSERT INTO `point_formulae_2009_10` VALUES (NULL,'Examiner: Px (50+3n)','50 + 3 x (n)',3,50);
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
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '1P1', 200);
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '2P1', 45);
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '3P1', 36);
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '1P2', 60);
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '1P3', 70);
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '1P4', 80);
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '1CW', 200);
INSERT INTO `studentspercourse_2009_10` VALUES (NULL, '2CW', 180);
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
  `ordering` int(11) default NULL COMMENT 'If a special order of units is desired, a number can be entered here.  A low number will put it near the top of the list and a high number lower down the list',
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
INSERT INTO `units_2009_10` VALUES (NULL,'1P1',NULL,NULL, '1','Year 1 Paper 1',NULL,'1','Note',0,1, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'2P1',NULL,NULL, '3','Year 2 Paper 1',NULL,'1','Note',0,NULL, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'3P1',NULL,NULL, '4','Year 3 Paper 1',NULL,'0','Note',0,NULL, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'1P2',NULL,NULL, '1','Year 1 Paper 2',NULL,'1','Note',0,NULL, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'1P3',NULL,NULL, '1','Year 1 Paper 3',NULL,'1','Note',0,NULL, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'1P4',NULL,NULL, '1','Year 1 Paper 4',NULL,'1','Note',0,NULL, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'1CW',NULL,NULL, '1,2,4','Year 1 Coursework',NULL,'1','Note',0,NULL, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'2CW',NULL,NULL, '1,3','Year 2 Coursework',NULL,'1','Note',0,NULL, NULL);
INSERT INTO `units_2009_10` VALUES (NULL,'Leave',NULL,NULL, '1,2,3,4,5','Staff on Leave',NULL,'1','Note',0,1, NULL);

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

