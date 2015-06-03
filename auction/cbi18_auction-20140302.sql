-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 02, 2014 at 11:21 PM
-- Server version: 5.5.28
-- PHP Version: 5.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cbi18_auction`
--

-- --------------------------------------------------------

--
-- Table structure for table `access`
--

CREATE TABLE IF NOT EXISTS `access` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(10) unsigned NOT NULL,
  `PrivId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58 ;

--
-- Dumping data for table `access`
--

INSERT INTO `access` (`Id`, `UserId`, `PrivId`) VALUES
(49, 1, 1),
(55, 7, 2),
(56, 8, 10),
(57, 9, 2);

-- --------------------------------------------------------

--
-- Table structure for table `bidders`
--

CREATE TABLE IF NOT EXISTS `bidders` (
  `id` int(11) NOT NULL,
  `first` varchar(32) NOT NULL,
  `last` varchar(32) NOT NULL,
  `email` varchar(64) NOT NULL,
  `address` varchar(32) NOT NULL,
  `phone` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE IF NOT EXISTS `bids` (
  `itemId` int(11) NOT NULL,
  `bidderId` int(11) NOT NULL,
  `bid` float NOT NULL,
  `bidTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `label`) VALUES
(2, 'Clothing & Accessories'),
(3, 'Artwork'),
(4, 'Business Services'),
(5, 'Children items'),
(6, 'Cooking'),
(7, 'Entertainment'),
(8, 'Event'),
(9, 'Food/Wine'),
(10, 'Gaming'),
(11, 'Get-Away'),
(12, 'Membership'),
(13, 'Personal Products'),
(14, 'Personal Service'),
(15, 'Personal Services'),
(16, 'Pet'),
(17, 'Private Dinners'),
(18, 'Private Outing'),
(19, 'Sporting Event');

-- --------------------------------------------------------

--
-- Table structure for table `challenge_record`
--

CREATE TABLE IF NOT EXISTS `challenge_record` (
  `challenge` varchar(64) NOT NULL DEFAULT '',
  `sess_id` varchar(64) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `challenge_record`
--

INSERT INTO `challenge_record` (`challenge`, `sess_id`, `timestamp`) VALUES
('03d4d406cd7d02210433523b01eabefae2a89d5a5ff95d52168d492058e05316', 'gfourfm35e8s6uor7444prog86', 1393827967);

-- --------------------------------------------------------

--
-- Table structure for table `event_log`
--

CREATE TABLE IF NOT EXISTS `event_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `type` char(32) NOT NULL,
  `userid` varchar(32) NOT NULL,
  `item` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `event_log`
--

INSERT INTO `event_log` (`id`, `time`, `type`, `userid`, `item`) VALUES
(1, '2014-02-23 15:58:02', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36'),
(2, '2014-02-23 16:55:39', 'logout', '1', 'session_id: gfourfm35e8s6uor7444prog86'),
(3, '2014-02-23 16:55:46', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36'),
(4, '2014-03-02 11:43:05', 'logout', '1', 'session_id: gfourfm35e8s6uor7444prog86'),
(5, '2014-03-02 11:43:13', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36'),
(6, '2014-03-02 14:16:46', 'logout', '1', 'session_id: gfourfm35e8s6uor7444prog86'),
(7, '2014-03-02 14:16:52', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36'),
(8, '2014-03-02 14:17:39', 'logout', '1', 'session_id: gfourfm35e8s6uor7444prog86'),
(9, '2014-03-02 14:17:50', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36'),
(10, '2014-03-02 21:28:27', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:24.0) Gecko/20100101 Firefox/24.0'),
(11, '2014-03-02 21:29:18', 'logout', '1', 'session_id: arsgc5vs8lfqs5vqi34epv0b55'),
(12, '2014-03-02 21:29:24', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:24.0) Gecko/20100101 Firefox/24.0'),
(13, '2014-03-02 21:35:18', 'logout', '1', 'session_id: arsgc5vs8lfqs5vqi34epv0b55'),
(14, '2014-03-02 21:37:56', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:24.0) Gecko/20100101 Firefox/24.0'),
(15, '2014-03-02 21:39:28', 'logout', '1', 'session_id: arsgc5vs8lfqs5vqi34epv0b55'),
(16, '2014-03-02 21:39:34', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:24.0) Gecko/20100101 Firefox/24.0'),
(17, '2014-03-02 21:53:25', 'logout', '1', 'session_id: gfourfm35e8s6uor7444prog86'),
(18, '2014-03-02 21:53:32', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36'),
(19, '2014-03-02 22:21:03', 'logout', '1', 'session_id: gfourfm35e8s6uor7444prog86'),
(20, '2014-03-02 22:21:15', 'login', '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `financial`
--

CREATE TABLE IF NOT EXISTS `financial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `multiplier` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `financial`
--

INSERT INTO `financial` (`id`, `multiplier`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 10),
(7, 15),
(8, 20),
(9, 30),
(10, 60),
(11, 100),
(12, 200),
(13, 300),
(14, 400),
(15, 500);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemCategory` int(11) NOT NULL,
  `itemType` varchar(4) DEFAULT NULL,
  `itemTitle` varchar(32) NOT NULL,
  `itemDesc` varchar(264) DEFAULT NULL,
  `itemValue` float DEFAULT NULL,
  `itemExpires` date DEFAULT NULL,
  `itemDonor` varchar(30) DEFAULT NULL,
  `bidOpen` float DEFAULT NULL,
  `bidIncrement` float DEFAULT NULL,
  `bidCurrent` float NOT NULL,
  `closed` tinyint(1) NOT NULL,
  `notes` varchar(34) DEFAULT NULL,
  `contact` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=60 ;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `itemCategory`, `itemType`, `itemTitle`, `itemDesc`, `itemValue`, `itemExpires`, `itemDonor`, `bidOpen`, `bidIncrement`, `bidCurrent`, `closed`, `notes`, `contact`) VALUES
(1, 6, 'Cert', '', '2-hour hands-on thai cooking lesson with cbi''s chef wendy in her kitchen', 100, '0000-00-00', 'Wendy Arenson', 0, 0, 0, 0, '', ''),
(2, 10, 'Cert', '', '2-hour Taiwanese mahjong lesson with cbi''s mahjong master vicki', 100, '0000-00-00', 'Vicki Ast', 0, 0, 0, 0, 'Up to 4 guests', ''),
(3, 18, 'Cert', '', 'Yachting excursion with CBI''s skipper Mark', 0, '0000-00-00', '*Mark Bialy', 0, 0, 0, 0, 'Up to 6 guests', ''),
(4, 12, 'Cert', '', '1-month membership to Krav Maga of Orange County, Irvine or Mission Viejo (original G.C. at Erica''s house)', 0, '0000-00-00', 'Biorki', 0, 0, 0, 0, '', ''),
(5, 17, 'Cert', '', '2-hour wine & cheese in the Blens'' backyard vineyard plus introduction to growing grapes and vine cutting with directions', 100, '0000-00-00', 'Blens', 0, 0, 0, 0, '', ''),
(6, 15, 'Cert', '', '1-hour live jazz set with drums, bass, and piano by CBI''s own cedars for your party or event', 500, '0000-00-00', 'Cedars', 0, 0, 0, 0, '', ''),
(7, 15, 'Cert', '', '2x 2-hour art lessons each for 1-4 children ages 7-12 at the home art studio of CBI''s multimedia artist Sharon, 2 x $200, minimum bid $100 each', 0, '0000-00-00', 'Sharon  Chase', 0, 0, 0, 0, '', ''),
(8, 6, 'Cert', '', '2-hour hands-on Italian cooking lessons in the cucina of CBI''s chef Leanne', 100, '0000-00-00', 'Leanne Cohen', 0, 0, 0, 0, '', ''),
(9, 15, 'Cert', '', '2-hour baseball coaching with CBI''s coach Gary', 100, '0000-00-00', 'Gary Cohen', 0, 0, 0, 0, '1-2 kids ages 8-14', ''),
(10, 15, 'Cert', '', '2-hour clay bead making lesson with CBI''s Anna', 50, '0000-00-00', 'Anna Eisen', 0, 0, 0, 0, '2 children ages 6 and up or adults', ''),
(11, 18, 'Cert', '', 'Bay cruise with lunch or cocktail hour aboard skipper Roberta''s Duffy', 0, '0000-00-00', '*Robert Feurstein', 0, 0, 0, 0, 'Up to 8 guests', ''),
(12, 6, 'Cert', '', '2-hour holiday baking and crafting session  with the Fisher girls', 100, '0000-00-00', 'Lisa Hatzkin Fisher S', 0, 0, 0, 0, 'Up to 4 kids ages 5-16', ''),
(13, 9, 'Item', '', 'Wine basket selected by CBI''s own sommelier Michel (to be provided by Cindy Furst)', 0, '0000-00-00', 'Fursts', 0, 0, 0, 0, '', ''),
(14, 18, 'Cert', '', '2-hour guided hike with CBI''s intrepid Cindy in the hills of Orange County', 100, '0000-00-00', 'Cindy Furst', 0, 0, 0, 0, '', ''),
(15, 2, 'Item', '', '25 hand-made greeting cards for every occasion by CBI''s Lila', 100, '0000-00-00', 'Lila Ginsburg', 0, 0, 0, 0, '', ''),
(16, 15, 'Cert', '', '2-hour tutoring session for a student in grades K-8 with CBI''s michelle', 100, '0000-00-00', 'Michelle Ginsburg', 0, 0, 0, 0, '', ''),
(17, 15, 'Cert', '', '1.5-hour pilates session with CBI''s Leslie tailored to your goals and experience,', 100, '0000-00-00', 'Leslie Kaufman', 0, 0, 0, 0, '', ''),
(18, 15, 'Cert', '', '2-hour organization session for your kitchen, office, or garage with clutter master leslie, priceless', 0, '0000-00-00', 'Leslie Kaufman', 0, 0, 0, 0, '', ''),
(19, 18, 'Cert', '', '2-hour hiking expedition guided by CBI''s health & fitness guru Michelle', 100, '0000-00-00', 'Michelle Lewis', 0, 0, 0, 0, '', ''),
(20, 15, 'Cert', '', '1 certificate for 2 45-minute personal training sessions (intake, assessment, goal setting) with CBI''s own professional, michelle,', 1140, '0000-00-00', 'Michelle Madick', 0, 0, 0, 0, '', ''),
(21, 8, 'Cert', '', '2 tickets & more for human rights watch''s annual voices of justice dinner in November in Los Angeles, date? TBA', 0, '0000-00-00', 'Hava Manasse', 0, 0, 0, 0, '', ''),
(22, 19, 'Cert', '', '4 angels tickets with parking, date TBD', 0, '0000-00-00', '*Morrisons', 0, 0, 0, 0, '', ''),
(23, 6, 'Cert', '', '2-hour Mediterranean starters hands-on lesson with CBI''s own chef Miriam,', 100, '0000-00-00', 'Miriam Ninyo', 0, 0, 0, 0, '', ''),
(24, 15, 'Cert', '', '1-hour basketball coaching  with CBI''s own pro referee Abby', 100, '0000-00-00', 'Abby Pezzner', 0, 0, 0, 0, '4 kids ages 5-12', ''),
(25, 18, 'Cert', '', '2-hour Laguna Canyon hike with CBI''s own OC native, Susan Seely', 100, '0000-00-00', 'Susan Seely', 0, 0, 0, 0, '', ''),
(26, 15, 'Cert', '', '3-hour babysitting session with CBI''s Clara & a friend', 0, '0000-00-00', 'Clara Seely-Katz', 0, 0, 0, 0, '', ''),
(27, 16, 'Item', '', 'Dead sea pet products basket, specifics TBD', 0, '0000-00-00', 'Debbie Sklar', 0, 0, 0, 0, '', ''),
(28, 15, 'Cert', '', '2-hour college essay session for a high school junior or senior with CBI''s editor Erica', 100, '0000-00-00', 'Erica Taylor', 0, 0, 0, 0, '', ''),
(29, 15, 'Cert', '', '2-hour photography session in a Spanish hacienda garden, you bring the photographer and equipment', 0, '0000-00-00', 'Erica Taylor', 0, 0, 0, 0, '', ''),
(30, 11, 'Cert', '', '1 week vacation at a cute & cozy big bear cabin on the lake', 1300, '0000-00-00', '*Trina Smith', 0, 0, 0, 0, '', ''),
(31, 11, 'Cert', '', '1 weekend get-away at a lovely cabin on the big bear lake', 400, '0000-00-00', 'Trina Smith', 0, 0, 0, 0, '', ''),
(32, 5, 'Item', '', 'Discovery science center family pack basket with 4-pack of tickets, 7 hands-on kids'' science projects (basket at Erica''s house)\r[ thank you to: Mike Fuhr, senior director of corporate relations, discovery science center, 2500 north main street, Santa Ana ca 92705]', 125, '0000-00-00', 'Jay Witzling', 0, 0, 0, 0, '', ''),
(33, 4, 'Cert', '', 'Gift Certificate for 100 greeting cards from your own design by CBI''s own pro printers the wolfs (1 of 2)', 100, '0000-00-00', 'Wolfs', 0, 0, 0, 0, '', ''),
(34, 4, 'Cert', '', 'Gift Certificate for 100 greeting cards from your own design by CBI''s own pro printers the wolfs (2 of 2)', 100, '0000-00-00', 'Wolfs', 0, 0, 0, 0, '', ''),
(35, 15, 'Cert', '', 'Sketching and oil pastel lesson, 1 hour for k-2 student or 2 hours for a grade 3-6 student with CBI''s extraordinary Helaine', 100, '0000-00-00', 'Helaine Yeskel', 0, 0, 0, 0, '', ''),
(36, 13, 'Item', '', 'Set of Pureology products', 0, '0000-00-00', 'Allen James Salon', 0, 0, 0, 0, '', ''),
(37, 3, 'Item', '', 'Will deliver frames to CBI', 0, '0000-00-00', 'Randi Bernstein', 0, 0, 0, 0, '', ''),
(38, 6, 'Cert', '', '2-hour Israeli cooking with Liora, value', 100, '0000-00-00', 'Liora Cohen', 0, 0, 0, 0, '', ''),
(39, 8, 'item', '', 'NTAC tickets', 0, '0000-00-00', 'Rae Cohen', 0, 0, 0, 0, '', ''),
(40, 15, 'Cert', '', '3 visits to personal trainer for health & fitness program', 600, '0000-00-00', 'Katherine Coster', 0, 0, 0, 0, '', ''),
(41, 8, 'Cert', '', 'Prime, reserved decorated picnic table for up to 8 @ holy Shabbat hootenanny, value', 360, '0000-00-00', '*CSP (Arie Katz, The Brenners)', 0, 0, 0, 0, '', ''),
(42, 15, 'Cert', '', '2-hour fabulous kugel lesson', 100, '0000-00-00', 'Jack Eisen', 0, 0, 0, 0, '', ''),
(43, 3, 'Item', '', 'Painting to be delivered, value', 0, '0000-00-00', 'The Frankels', 0, 0, 0, 0, '', ''),
(44, 3, 'Item', '', 'Framed art photos being prepared, value', 0, '0000-00-00', 'Joel Gallin', 0, 0, 0, 0, '', ''),
(45, 15, 'Cert', '', '2-hour bridge lesson up to 4 guests with professional instructor', 200, '0000-00-00', 'Mitchell Goldman', 0, 0, 0, 0, '', ''),
(46, 12, 'Cert', '', '6-month family membership for new family', 785, '0000-00-00', '*JCC', 0, 0, 0, 0, '', ''),
(47, 9, 'Item', '', 'Tequila basket to be delivered', 0, '0000-00-00', 'The Michaels', 0, 0, 0, 0, '', ''),
(48, 13, 'Item', '', 'Beats headphones (received by Sandy)', 0, '0000-00-00', 'Eric Mirowitz', 0, 0, 0, 0, '', ''),
(49, 3, 'Item', '', 'Object to be donated on march 21 (don''t ask...)', 0, '0000-00-00', 'Molly Wood Garden', 0, 0, 0, 0, '', ''),
(50, 7, 'Item', '', '4 sea explorer cruise passes and 4 ocean institute admissions,', 166, '0000-00-00', 'Ocean Institute', 0, 0, 0, 0, '', ''),
(51, 15, 'Cert', '', '1-hour Hebrew lesson, 1-hour prek-6 tutoring, 1-hour math lesson PreK - 4', 0, '0000-00-00', 'Iona Pally', 0, 0, 0, 0, '', ''),
(52, 4, 'Cert', '', 'Complimentary oil change', 0, '0000-00-00', 'Power Toyota', 0, 0, 0, 0, '', ''),
(53, 5, 'Cert', '', 'Henna tattoos for children''s party, up to 3 hours', 150, '0000-00-00', 'Hannah Reinhard', 0, 0, 0, 0, '', ''),
(54, 7, 'Cert', '', 'Magical Disneyland day for 4 with both parks, club 33, and valet parking', 0, '0000-00-00', '*Schneiders', 0, 0, 0, 0, '', ''),
(55, 19, 'Cert', '', '4 angels stadium seats in the dugout suite next to the warm-up circle at field level,', 1200, '0000-00-00', '*Debbie Kornsweet Shandling', 0, 0, 0, 0, '', ''),
(56, 15, 'Cert', '', '2-hour sat lesson', 0, '0000-00-00', 'Nonna Sheynkman', 0, 0, 0, 0, '', ''),
(57, 4, 'Cert', '', '1 physical exam + 1 bath + goodies', 0, '0000-00-00', 'Stonecreek Animal Hospital', 0, 0, 0, 0, '', ''),
(58, 6, 'Cert', '', '2-hour tamale making lesson', 0, '0000-00-00', 'Diana Velazquez', 0, 0, 0, 0, '', ''),
(59, 6, '', '', '2-hour chocolate mousse lesson', 0, '0000-00-00', 'Gila Wilner', 0, 0, 0, 0, 'Up to 5 friends', '');

-- --------------------------------------------------------

--
-- Table structure for table `pledges`
--

CREATE TABLE IF NOT EXISTS `pledges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pledgeType` tinyint(4) NOT NULL,
  `firstName` char(32) NOT NULL,
  `lastName` char(32) NOT NULL,
  `phone` bigint(20) NOT NULL,
  `email` char(64) NOT NULL,
  `paymentMethod` tinyint(4) NOT NULL,
  `amount` float NOT NULL,
  `pledgeIds` char(128) NOT NULL,
  `pledgeOther` varchar(512) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=267 ;

--
-- Dumping data for table `pledges`
--

INSERT INTO `pledges` (`id`, `pledgeType`, `firstName`, `lastName`, `phone`, `email`, `paymentMethod`, `amount`, `pledgeIds`, `pledgeOther`, `timestamp`) VALUES
(33, 3, '', '', 0, '', 0, 65000, '', '', '2013-06-18 05:24:46'),
(80, 1, 'Andrew', 'Elster', 9495096711, 'andy@elsternet.com', 1, 540, '', '', '2013-08-20 05:44:50'),
(81, 1, 'Beth', 'Elster', 9495096711, 'beth@elsternet.com', 3, 540, '', '', '2013-08-20 05:46:22'),
(82, 2, 'Beth', 'Elster', 9495096711, 'beth@elsternet.com', 0, 0, '3,7', '', '2013-08-20 05:48:44'),
(83, 2, 'Andrew', 'Elster', 9495096711, 'andy@elsternet.com', 0, 0, '15,17', '', '2013-08-20 05:48:50'),
(84, 1, 'Steve, Andrea, Liam, & Adele', 'Alfi', 7142269625, 'andrea.alfi@gmail.com', 3, 72, '', '', '2013-08-20 23:07:49'),
(85, 2, 'Steve, Andrea, Liam, & Adele', 'Alfi', 7142269625, 'andrea.alfi@gmail.com', 0, 0, '1,3,5,8,13,15', '', '2013-08-20 23:09:15'),
(86, 1, 'Julia and Ken', 'Reinhard', 9498545611, 'jrlupton@uci.edu', 3, 180, '', '', '2013-08-21 16:59:51'),
(87, 2, 'Julia and Ken', 'Reinhard', 9498545611, 'jrlupton@uci.edu', 0, 0, '', 'Be a better Board member!!', '2013-08-21 17:00:55'),
(88, 2, 'Michelle', 'Madick', 7144572145, 'xoxo@cox.net', 0, 0, '9', '', '2013-08-21 17:28:57'),
(89, 1, 'Michelle', 'Madick', 7144572145, 'xoxo@cox.net', 3, 180, '', '', '2013-08-21 17:29:42'),
(90, 1, 'Jonathan', 'Cohen', 9498563607, 'jkcohen@pobox.com', 2, 72, '', '', '2013-08-21 18:05:53'),
(91, 2, 'Nessim', 'Albagli', 9492499451, 'nessim.albagli@yahoo.com', 0, 0, '4,6,8,9', '', '2013-08-21 21:24:39'),
(92, 2, 'Nancy', 'Neudorf', 9498546684, 'nneudorf@gmail.com', 0, 0, '9,10,12,18', '', '2013-08-21 21:37:35'),
(93, 2, 'Susan', 'Seely', 9496751362, 'smseely1@mac.com', 0, 0, '3,5,8,9,11,15,17,18', '', '2013-08-22 03:50:18'),
(94, 1, 'Susan', 'Seely', 9496751362, 'smseely1@mac.com', 1, 54, '', '', '2013-08-22 03:50:43'),
(95, 1, 'Wendy', 'Kottmeier', 7143335228, 'Wrkottmeier@yahoo.com', 1, 54, '', '', '2013-08-22 04:47:04'),
(96, 1, 'Francine', 'Wenhardt', 7144179291, 'fwen@cox.net', 1, 540, '', '', '2013-08-22 13:31:45'),
(97, 2, 'Francine', 'Wenhardt', 7144179291, 'fwen@cox.net', 0, 0, '5,11,14,15,17,18', '', '2013-08-22 13:33:43'),
(98, 1, 'Joel', 'Widzer', 7145442855, 'jwidzer@cox.net', 1, 90, '', '', '2013-08-22 14:58:29'),
(99, 2, 'Mary Ann', 'Malkoff', 7143288541, 'maryann@malkoff.com', 0, 0, '1,5,15', '', '2013-08-22 15:40:49'),
(100, 1, 'Phyllis and Steven', 'Littman', 7147346832, 'phyllisabrams2@gmail.com', 1, 180, '', '', '2013-08-22 16:29:45'),
(101, 2, 'Phyllis and Steven', 'Littman', 7147346832, 'phyllisabrams2@gmail.com', 0, 0, '7,19', '', '2013-08-22 16:30:17'),
(102, 1, 'Sandy', 'Klein', 7147309693, 'sklein@cbi18.org', 2, 360, '', '', '2013-08-22 16:51:31'),
(103, 2, 'Sandy', 'Klein', 7147309693, 'sklein@cbi18.org', 0, 0, '9', '', '2013-08-22 16:52:12'),
(104, 1, 'Helene', 'Coulter', 7147309693, 'hcoulter@cbi18.org', 1, 18, '', '', '2013-08-22 16:57:43'),
(105, 2, 'Helene', 'Coulter', 7147309693, 'hcoulter@cbi18.org', 0, 0, '5,7,8,10,11,16,19', '', '2013-08-22 16:58:31'),
(106, 1, 'Denise', 'Mailman', 9499337567, 'denisemailman2@yahoo.com', 1, 54, '', '', '2013-08-22 20:55:37'),
(107, 1, 'Marc', 'Goldin', 9493482775, 'Goldinzim@hotmail.com', 3, 360, '', '', '2013-08-22 23:35:32'),
(108, 2, 'Marc', 'Goldin', 9493482775, 'Goldinzim@hotmail.com', 0, 0, '15,18', '', '2013-08-22 23:36:38'),
(109, 1, 'Yolande & Joe', 'Bati', 7147309693, 'hcoulter@cbi18.org', 3, 640, '', '', '2013-08-23 17:07:02'),
(110, 1, 'Judy', 'Thurmond', 7147309693, 'hcoulter@cbi18.org', 3, 500, '', '', '2013-08-23 17:07:35'),
(111, 1, 'Howard & Ingrid', 'Rosenthal', 7147309693, 'hcoulter@cbi18.org', 3, 900, '', '', '2013-08-23 17:08:15'),
(112, 1, 'Mike & Natalie', 'Vishny', 7147309693, 'hcoulter@cbi18.org', 3, 500, '', '', '2013-08-23 17:08:37'),
(113, 1, 'Howard and Ellen', 'Mirowitz', 9497591637, 'howard@mirowitz.com', 3, 180, '', '', '2013-08-23 18:40:28'),
(114, 2, 'Howard and Ellen', 'Mirowitz', 9497591637, 'howard@mirowitz.com', 0, 0, '3,6,8,11,17', '', '2013-08-23 18:41:42'),
(115, 1, 'Jay', 'Witzling', 9495527650, 'jlwitzling@cox.net', 1, 360, '', '', '2013-08-23 21:45:22'),
(118, 2, 'Eric', 'Dangott', 7149142431, 'edangott@hotmail.com', 0, 0, '1,5,7,8,11,17', '', '2013-08-25 23:29:23'),
(119, 1, 'Eric', 'Dangott', 7149142431, 'edangott@hotmail.com', 1, 1, '', '', '2013-08-25 23:30:14'),
(120, 1, 'Muriel', 'Ullman', 7143184038, 'Murielullman@yahoo.com', 1, 36, '', '', '2013-08-25 23:56:22'),
(121, 1, 'nessim', 'albagli', 9492499451, 'nessim.albagli@yahoo.com', 2, 270, '', '', '2013-08-26 02:52:30'),
(122, 1, 'Ann', 'Bendroff', 7142828632, 'Abendroff@gmail.com', 2, 18, '', '', '2013-08-26 03:41:19'),
(123, 2, 'Ann', 'Bendroff', 7142828632, 'Abendroff@gmail.com', 0, 0, '9,17', '', '2013-08-26 03:42:27'),
(124, 2, 'Terry', 'Ginsburg', 7144207667, 'arturo.gins@gmail.com', 0, 0, '7,15', '', '2013-08-26 03:47:44'),
(125, 1, 'Bonnie', 'Widerman', 9492629547, 'bonnielee10@hotmail.com', 3, 90, '', '', '2013-08-26 03:59:01'),
(126, 2, 'Bonnie', 'Widerman', 9492629547, 'bonnielee10@hotmail.com', 0, 0, '7', '', '2013-08-26 04:00:01'),
(127, 1, 'Terry', 'Ginsburg', 7144207667, 'arturo.gins@gmail.com', 1, 90, '', '', '2013-08-26 04:05:39'),
(128, 1, 'Joel', 'Kuperberg', 7146624608, 'kuplevin@cox.net', 1, 90, '', '', '2013-08-26 04:40:59'),
(129, 2, 'Joel', 'Kuperberg', 7146624608, 'kuplevin@cox.net', 0, 0, '8,14', '', '2013-08-26 04:41:42'),
(130, 2, 'Michael', 'Adler', 7148388480, 'adler.family@cox.net', 0, 0, '8,15,17', '', '2013-08-26 06:44:43'),
(131, 1, 'Margot and Michael', 'Shapiro', 9493552635, 'margot@shapiro5.com', 1, 270, '', '', '2013-08-26 07:20:19'),
(132, 2, 'Margot and Michael', 'Shapiro', 9493552635, 'margot@shapiro5.com', 0, 0, '9', '', '2013-08-26 07:21:25'),
(133, 1, 'Heather', 'Katz', 7148325677, 'heather@tkoart.com', 2, 540, '', '', '2013-08-26 18:04:12'),
(134, 2, 'Heather', 'Katz', 7148325677, 'heather@tkoart.com', 0, 0, '11,15', '', '2013-08-26 18:05:03'),
(135, 1, 'Joel', 'Don', 9498588793, 'jcdon460@hotmail.com', 2, 180, '', '', '2013-08-27 04:47:52'),
(136, 2, 'Lisa', 'Shatzkin', 7143894491, 'lshatzkin@yahoo.com', 0, 0, '5,8,10,12,14,15,16', '', '2013-08-27 14:08:46'),
(137, 2, 'Nancy', 'Raymon', 9492944407, 'nancyraymon@gmail.com', 0, 0, '', 'This is the year for me to find ways in which I can contribute to the health, healing and well-being of members of the CBI Family. I''m so looking forward to using my experience and skills in fulfilling this pledge, whatever form it may take. Nancy Raymon', '2013-08-27 18:41:43'),
(139, 1, 'Marla & Scott', 'Nathan', 7145054115, 'mnathan@gr8law.com', 3, 360, '', '', '2013-08-28 20:18:25'),
(140, 2, 'Marla & Scott', 'Nathan', 7145054115, 'mnathan@gr8law.com', 0, 0, '8,10,11,14,15,17,18', '', '2013-08-28 20:20:02'),
(141, 2, 'Steven', 'Neudorf', 9498546684, 'Teamneudorf@gmail.com', 0, 0, '18', '', '2013-08-28 20:26:12'),
(144, 1, 'Harris and Janice', 'Shultz', 9495518620, 'hshultz@cox.net', 1, 360, '', '', '2013-08-29 22:23:20'),
(145, 2, 'Harris', 'Shultz', 9495518620, 'hshultz@cox.net', 0, 0, '5,6,8,18,19', '', '2013-08-29 22:25:40'),
(146, 1, 'Jack and Elaine', 'Finkelstein', 7145303673, 'jack55elaine@yahoo.com', 1, 90, '', '', '2013-08-29 23:34:21'),
(147, 1, 'Debbie', 'Moysychyn', 7143252623, 'd_moysychyn@hotmail.com', 1, 180, '', '', '2013-08-30 00:29:34'),
(148, 1, 'Gavin', 'Jonas', 9497862214, 'Gijonas@cox.net', 1, 180, '', '', '2013-08-30 01:27:29'),
(149, 1, 'Tony and Jean', 'Kravitz', 9493482949, 'tony.kravitz@unisys.com', 1, 54, '', '', '2013-08-30 02:45:13'),
(150, 1, 'David & Joyce', 'Walter', 7144013958, 'Joysdrm@flash.net', 1, 90, '', '', '2013-08-30 03:27:12'),
(151, 2, 'David & Joyce', 'Walter', 7144013958, 'Joysdrm@flash.net', 0, 0, '1,8,13', '', '2013-08-30 03:29:05'),
(153, 1, 'Michael', 'Adler', 7148388480, 'adler.family@cox.net', 1, 18, '', '', '2013-08-30 05:58:30'),
(154, 1, 'Annie & Jeff', 'Shugarman', 7146652225, 'jeffandannie@sbcglobal.net', 1, 180, '', '', '2013-08-30 15:11:30'),
(155, 2, 'natalie', 'vishny', 9494331822, 'mvishny@aol.com', 0, 0, '', 'Make sure that our older friends inside and outside of our CBI community receive support', '2013-08-30 16:39:31'),
(156, 1, 'Lawrence', 'Wayne', 9496798879, 'waynelg@cox.net', 3, 360, '', '', '2013-08-30 17:41:23'),
(157, 1, 'Francine & Ron', 'Morrison', 9495096678, 'romotustin@aol.com', 2, 360, '', '', '2013-08-30 18:37:24'),
(158, 1, 'Ayal & Gila', 'Willner', 7147309693, 'hcoulter@cbi18.org', 2, 522, '', '', '2013-08-30 19:05:38'),
(159, 2, 'JEREMY', 'SEGAL', 7143894970, 'jeremydsegal@gmail.com', 0, 0, '3,5,6,8,15', '', '2013-08-30 23:56:53'),
(160, 1, 'JEREMY', 'SEGAL', 7143894970, 'jeremydsegal@gmail.com', 1, 36, '', '', '2013-08-30 23:57:21'),
(161, 1, 'Mike and Sheila', 'Lefkowitz', 9499030820, 'Lefkowitzmike@gmail.com', 1, 360, '', '', '2013-08-31 17:48:57'),
(162, 1, 'Barbara', 'Zwart', 7144862658, 'Barbzwart@aol.com', 2, 90, '', '', '2013-09-01 04:19:02'),
(163, 1, 'Adina', 'Stowell', 9496792799, 'alwitzling@yahoo.com', 1, 360, '', '', '2013-09-01 18:27:11'),
(164, 1, 'Arvin & Beth', 'Katlen', 7144340606, 'arvnbec@att.net', 1, 1800, '', '', '2013-09-01 19:35:15'),
(165, 1, 'Hannah', 'Wachs', 9493871765, 'hwachs@gmail.com', 1, 180, '', '', '2013-09-01 21:03:47'),
(166, 2, 'Hannah', 'Wachs', 9493871765, 'hwachs@gmail.com', 0, 0, '1,8', 'Make and deliver at least three meals as part of the Chesed committee', '2013-09-01 21:05:32'),
(167, 1, 'Perry', 'Bridger', 9497487213, 'perrybridger@yahoo.com', 1, 90, '', '', '2013-09-02 15:44:52'),
(168, 2, 'Perry', 'Bridger', 9497487213, 'perrybridger@yahoo.com', 0, 0, '8,11,15', '', '2013-09-02 15:45:30'),
(169, 1, 'Stacy', 'Leavitt', 9494661472, 'Leavitt.stacy@gmail.com', 1, 360, '', '', '2013-09-03 13:57:46'),
(170, 1, 'Rhonda and Hal', 'Hurwitz', 9498579400, 'rhonda@hurwitzhome.com', 1, 72, '', '', '2013-09-03 19:31:13'),
(171, 1, 'Shirley and Leonard', 'Kessler', 9497861008, 'rhonda@hurwitzhome.com', 1, 18, '', '', '2013-09-03 19:32:53'),
(181, 1, 'Harry - add to my account', 'Krebs', 7146360718, 'H428K@aol.com', 3, 1080, '', '', '2013-09-03 22:56:13'),
(182, 1, 'Mike', 'Mymon', 9497163266, 'mr3m2001@yahoo.com', 1, 36, '', '', '2013-09-03 22:56:59'),
(183, 2, 'Mike', 'Mymon', 9497163266, 'mr3m2001@yahoo.com', 0, 0, '3,5,8,15,18', 'Continue to be a Shamash once a month', '2013-09-03 22:59:16'),
(184, 1, 'Fred and Diane', 'Reiss', 9519265783, 'dfreiss@roadrunner.com', 3, 180, '', '', '2013-09-03 23:50:38'),
(191, 2, 'Cathy', 'Bardenstein', 5853708020, 'cjbardy@mac.com', 0, 0, '1,5,15', '', '2013-09-04 16:34:44'),
(192, 1, 'Cathy', 'Bardenstein', 5853708020, 'cjbardy@mac.com', 1, 36, '', '', '2013-09-04 16:35:35'),
(193, 1, 'Deborah', 'Goodman', 7145355585, 'deborahwgoodman@yahoo.com', 1, 36, '', '', '2013-09-04 17:35:53'),
(194, 1, 'Nanci', 'Patchen', 7145526435, 'nanci.patchen@roadrunner.com', 2, 90, '', '', '2013-09-04 18:38:11'),
(195, 2, 'Michael', 'Bare', 7147954552, 'michaelbare@yahoo.com', 0, 0, '1,3,5,14', '', '2013-09-04 18:40:23'),
(196, 1, 'Michael', 'Bare', 7147954552, 'michaelbare@yahoo.com', 1, 180, '', '', '2013-09-04 18:41:38'),
(197, 1, 'adrian', 'shandling', 5625983200, 'ashandling@aol.com', 3, 540, '', '', '2013-09-05 19:38:14'),
(198, 1, 'Paul & Carol', 'Lehrer', 7149629853, 'lehrersc@aol.com', 2, 180, '', '', '2013-09-05 21:15:02'),
(199, 1, 'Steven', 'Cohen', 7145735835, 'e-s_cohen@prodigy.net', 1, 18, '', '', '2013-09-06 02:27:41'),
(200, 1, 'Larry', 'Danzig', 9496400370, 'sewheart@gmail.com', 2, 1800, '', '', '2013-09-06 03:17:42'),
(201, 1, 'Philip', 'Blen', 7147309469, 'pblen@cox.net', 3, 180, '', '', '2013-09-07 20:10:02'),
(203, 1, 'Howard and Judy', 'Brostoff', 7145444116, 'hjmj1@cox.net', 1, 540, '', '', '2013-09-08 01:51:51'),
(204, 1, 'Marshall', 'Margolis', 7149644507, 'margolismarshall@hotmail.com', 3, 180, '', '', '2013-09-08 02:14:08'),
(205, 1, 'Jane', 'Flynn', 9498880898, 'janeflynn3@gmail.com', 3, 54, '', '', '2013-09-08 15:41:53'),
(206, 1, 'Andrew and Esther', 'Dosick', 7147302224, 'dosick@gmail.com', 1, 180, '', '', '2013-09-08 15:54:22'),
(207, 1, 'Carl', 'Groner', 7143805150, 'Gronerfamily@gmail.com', 1, 18, '', '', '2013-09-08 16:37:02'),
(208, 1, 'Matthew and Dana', 'Sperling', 9493223328, 'dpsmsw@gmail.com', 1, 36, '', '', '2013-09-08 17:40:16'),
(209, 1, 'Norman & Pepita', 'Katz', 7147315403, 'katz2032@cox.net', 1, 1080, '', '', '2013-09-08 20:47:06'),
(210, 1, 'Johanna', 'Rose', 9497918226, 'jrodevelopment@gmail.com', 1, 1080, '', '', '2013-09-08 21:41:23'),
(211, 1, 'Benjamin + Sandie', 'Goelman', 7148384229, 'bengoelman@cox.net', 3, 3600, '', '', '2013-09-08 22:14:59'),
(212, 1, 'Charles', 'Samson', 9498618363, 'chalkysamson@cox.net', 1, 180, '', '', '2013-09-09 00:11:07'),
(213, 1, 'Bruce', 'Erenkrantz', 7147340084, 'bruce@erenkrantz.com', 1, 360, '', '', '2013-09-09 00:58:40'),
(214, 1, 'Mitch and Nancy', 'Moss', 7143689695, 'Ngmoss@yahoo.com', 1, 90, '', '', '2013-09-09 03:05:03'),
(215, 1, 'leslie', 'kaufman', 7146516192, 'wormfamily@cox.net', 2, 90, '', '', '2013-09-09 03:38:20'),
(217, 1, 'Edward & Fredda', 'Sussman', 7149687526, 'sussmanfv@verizon.net', 2, 540, '', '', '2013-09-09 14:28:03'),
(218, 2, 'Edward & Fredda', 'Sussman', 7149687526, 'sussmanfv@verizon.net', 0, 0, '4,8,11', '', '2013-09-09 14:29:36'),
(219, 1, 'ann', 'bialy', 5860288, 'abialy@gmail.com', 1, 180, '', '', '2013-09-09 14:36:51'),
(220, 1, 'Michael', 'Rosenthal', 9497668094, 'mikerose@cox.net', 1, 180, '', '', '2013-09-09 16:44:07'),
(221, 2, 'Gila', 'Willner', 9496777005, 'gilawillner@earthlink.net', 0, 0, '5,6,11,15', '', '2013-09-09 17:25:52'),
(222, 1, 'Herman', 'Birch', 7147309693, 'hcoulter@cbi18.org', 2, 3600, '', '', '2013-09-09 19:22:11'),
(223, 1, 'Bob', 'Messe', 7147309693, 'hcoulter@cbi18.org', 3, 100, '', '', '2013-09-09 19:22:37'),
(224, 1, 'Hayim and Miriam', 'Ninyo', 7145440995, 'mirninyo@gmail.com', 1, 360, '', '', '2013-09-09 19:42:15'),
(225, 1, 'larry', 'passo', 9495590283, '125deb.passo@gmail.com', 1, 54, '', '', '2013-09-09 20:31:26'),
(226, 1, 'David & Phyllis', 'Iser', 7147722298, 'Philjoys@aol.com', 1, 90, '', '', '2013-09-09 20:54:28'),
(227, 1, 'Phyllis & Al', 'Steinberg', 7148428029, 'a2steinberg@verizon.net', 3, 180, '', '', '2013-09-09 20:56:50'),
(228, 2, 'Al', 'Steinberg', 7148428029, 'a2steinberg@verizon.net', 0, 0, '4,8', '', '2013-09-09 20:57:33'),
(229, 1, 'Michael', 'Wolf', 9497069907, 'ymikewolf@yahoo.com', 1, 256, '', '', '2013-09-09 20:58:17'),
(230, 1, 'Ahuva and Winston', 'Ho', 7142891410, 'savtaho@yahoo.com', 1, 1080, '', '', '2013-09-09 21:07:57'),
(231, 1, 'Cynthia', 'Furst', 9498541235, 'cindy.furst@cox.net', 1, 1800, '', '', '2013-09-09 21:30:21'),
(232, 1, 'Sharon', 'Refael', 7146658223, 'Sharonrefael@att.net', 1, 180, '', '', '2013-09-09 21:57:56'),
(233, 2, 'Sharon and Doron', 'Refael', 7146658223, 'Sharonrefael@att.net', 0, 0, '8', '', '2013-09-09 22:00:53'),
(234, 1, 'Sue ann', 'Cross', 9496736777, 'Cross.sa@sbcglibal.net', 1, 540, '', '', '2013-09-09 22:37:36'),
(235, 1, 'Paula', 'Goldberg', 7148383995, 'Pfzgold@gmail.com', 2, 90, '', '', '2013-09-09 23:19:10'),
(236, 1, 'Gary & Phyllis', 'Segal', 7147309693, 'hcoulter@cbi18.org', 2, 500, '', '', '2013-09-09 23:24:49'),
(237, 1, 'Katherine', 'Coster', 9493876542, 'Katherine.coster@gmail.com', 1, 90, '', '', '2013-09-09 23:44:22'),
(245, 1, 'Leane', 'Kahrs', 7145732521, 'lkahrs223@gmail.com', 1, 540, '', '', '2013-09-10 01:11:14'),
(246, 1, 'Brian & Sarah', 'Chisick', 7145442712, 'bc41339@gmail.com', 3, 2500, '', '', '2013-09-10 02:04:09'),
(247, 1, 'Blossom', 'Siegel', 9496452334, 'savtablos@earthlink.net', 1, 1800, '', '', '2013-09-10 02:45:14'),
(248, 1, 'Sandy and Earl', 'Stein', 9495523779, 'ethep18@aol.com', 2, 180, '', '', '2013-09-10 03:05:06'),
(249, 1, 'Pamela', 'Kauss', 7147440602, 'Winwaysdirector@aol.com', 2, 360, '', '', '2013-09-10 03:43:10'),
(250, 1, 'Zane', 'Gerber', 9494542898, 'zanegerber@cox.net', 1, 180, '', '', '2013-09-10 04:36:03'),
(251, 2, 'Joel', 'Reiss', 7143120921, 'Jreiss18@gmail.com', 0, 0, '5,8', '', '2013-09-10 04:37:38'),
(252, 2, 'Lilya', 'Reiss', 7143120921, 'Lilyar17@gmail.com', 0, 0, '8,17', '', '2013-09-10 04:38:06'),
(253, 1, 'Joel and Lilya', 'Reiss', 7143120921, 'Jreiss18@gmail.com', 2, 7200, '', '', '2013-09-10 04:38:43'),
(254, 1, 'Jonathan', 'Fine', 7148386636, 'jmfine2000@gmail.com', 1, 72, '', '', '2013-09-10 04:43:29'),
(255, 2, 'Jonathan', 'Fine', 7148386636, 'jmfine2000@gmail.com', 0, 0, '18', '', '2013-09-10 04:44:17'),
(256, 1, 'Eileen', 'Slipakoff', 7143525645, 'Esf3s3@aol.com', 2, 180, '', '', '2013-09-10 04:44:50'),
(257, 1, 'Matthew', 'Brenner', 9497060475, 'mhbrenner0900@mac.com', 1, 540, '', '', '2013-09-10 04:47:13'),
(258, 1, 'David & Ofra', 'Willner', 9498375236, 'kc1et@cox.net', 1, 54, '', '', '2013-09-10 05:09:35'),
(259, 1, 'Morris', 'FLEISHMAN', 9497683940, 'bumper70@cox.net', 1, 36, '', '', '2013-09-10 05:58:58'),
(260, 1, 'LISA AND MICHAEL', 'SHATZKIN/ FISCHER', 7143894491, 'MICHAELANDLISA@COX.NET', 1, 36, '', '', '2013-09-10 16:05:24'),
(261, 1, 'Edita', 'Szekely', 7147309693, 'hcoulter@cbi18.org', 2, 36, '', '', '2013-09-10 20:16:32'),
(262, 1, 'Shirley', 'Shlachter', 7147309693, 'hcoulter@cbi18.org', 1, 36, '', '', '2013-09-10 21:38:14'),
(263, 1, 'John and Helaine', 'Yeskel', 9492612332, 'jayeskel@cox.net', 2, 180, '', '', '2013-09-11 02:44:49'),
(264, 1, 'Blake', 'Michaels', 9494228561, 'blakesterr1@yahoo.com', 2, 270, '', '', '2013-09-11 03:13:45'),
(265, 1, 'Blake', 'Michaels', 9494228561, 'blakesterr1@yahoo.com', 2, 270, '', '', '2013-09-11 03:14:56'),
(266, 1, 'cindy', 'jacobs', 7147308273, 'xleftiesbrigade@yahoo.com', 1, 100, '', '', '2013-09-11 03:48:37');

-- --------------------------------------------------------

--
-- Table structure for table `privileges`
--

CREATE TABLE IF NOT EXISTS `privileges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `privileges`
--

INSERT INTO `privileges` (`id`, `name`, `level`, `enabled`) VALUES
(1, 'control', 500, 1),
(2, 'admin', 400, 1),
(10, 'office', 300, 1);

-- --------------------------------------------------------

--
-- Table structure for table `spiritual`
--

CREATE TABLE IF NOT EXISTS `spiritual` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `spiritualType` tinyint(4) NOT NULL,
  `description` char(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `spiritual`
--

INSERT INTO `spiritual` (`id`, `spiritualType`, `description`) VALUES
(1, 1, 'Learn to read Hebrew or increase my Hebrew skills'),
(2, 1, 'Learn to put on a tallit or tefillin'),
(3, 1, 'Attend Adult Education classes'),
(4, 1, 'Celebrate an Adult Bar/Bat Mitzvah'),
(5, 2, 'Chant Torah/Haftorah'),
(6, 2, 'Attend Sunday and/or Wednesday morning Minyan'),
(7, 2, 'Learn to lead services'),
(8, 2, 'Attend Shabbat Services'),
(9, 2, 'Incorporate meditation into my day'),
(10, 3, 'Prepare food for the Family Promise program'),
(11, 3, 'Attend a Shivah Minyan'),
(12, 3, 'Join the Chessed committee'),
(13, 3, 'Visit the elderly or ill'),
(14, 3, 'Become an usher at Shabbat Services'),
(15, 3, 'Invite guests for Shabbat Dinner'),
(16, 3, 'Become a Buddy for a new family'),
(17, 3, 'Volunteer at Sunday supper'),
(18, 3, 'Donate blood'),
(19, 3, 'Volunteer in the CBI office');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last` varchar(16) NOT NULL,
  `first` varchar(16) NOT NULL,
  `email` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pwdchanged` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pwdexpires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `username`, `active`, `last`, `first`, `email`, `password`, `lastlogin`, `pwdchanged`, `pwdexpires`) VALUES
(1, 'control', 1, 'Elster', 'Andy', 'andy.elster@gmail.com', '538f814cd2d0e0d34688e4f1b402832b147fa371693433d62685c9d73c03665c', '2014-03-02 22:21:15', '2014-02-23 23:38:54', '2014-05-01 23:21:15'),
(7, 'beth', 1, 'Elster', 'Beth', 'beth@elsternet.com', '3b41ed68ffc6fa4c109a15b521a382d340ac760cb658d52236307f6c3199e283', '2013-09-10 16:11:04', '2013-09-09 20:57:43', '2013-11-09 15:11:04'),
(8, 'helene', 1, 'Coulter', 'Helene', 'hcoulter@cbi18.org', 'f95c29121f9e56838963269c5a4ba53c5232b3ba8413bbafcdfe96076f9ef1b9', '2013-09-10 16:27:16', '2013-08-23 12:50:02', '2013-11-09 15:27:16'),
(9, 'andy', 1, 'Elster', 'Andy', 'andy@elsternet.com', '538f814cd2d0e0d34688e4f1b402832b147fa371693433d62685c9d73c03665c', '2013-09-10 16:03:23', '2013-08-26 23:40:48', '2013-11-09 15:03:23');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
