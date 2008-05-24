--
-- Table structure for table `wc_comments`
--

DROP TABLE IF EXISTS `wc_comments`;
CREATE TABLE `wc_comments` (
  `comment_id` int(10) unsigned NOT NULL auto_increment,
  `post_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `comment_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  `ip_address` varchar(15) NOT NULL default '',
  `status` enum('APPROVED','UNAPPROVED','SPAM') NOT NULL default 'APPROVED',
  PRIMARY KEY  (`comment_id`),
  KEY `post_date_status` (`post_id`,`status`,`comment_date`),
  KEY `status_date` (`status`,`comment_date`),
  KEY `comment_date` (`comment_date`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Table structure for table `wc_pages`
--

DROP TABLE IF EXISTS `wc_pages`;
CREATE TABLE `wc_pages` (
  `page_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `body` text NOT NULL,
  `nav_label` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`page_id`),
  KEY `nav_label` (`nav_label`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Table structure for table `wc_posts`
--

DROP TABLE IF EXISTS `wc_posts`;
CREATE TABLE `wc_posts` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `subject` varchar(100) NOT NULL default '',
  `body` text NOT NULL,
  `post_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(10) unsigned NOT NULL default '0',
  `uri` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`post_id`),
  KEY `post_date` (`post_date`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Table structure for table `wc_tags`
--

DROP TABLE IF EXISTS `wc_tags`;
CREATE TABLE `wc_tags` (
  `post_id` int(10) unsigned NOT NULL default '0',
  `tag` varchar(20) default NULL,
  UNIQUE KEY `post_id` (`post_id`,`tag`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `wc_users`
--

DROP TABLE IF EXISTS `wc_users`;
CREATE TABLE `wc_users` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `user_name` varchar(20) NOT NULL default '',
  `first_name` varchar(25) NOT NULL default '',
  `last_name` varchar(25) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `about` text NOT NULL,
  `session_id` varchar(72) NOT NULL default '',
  `password` varchar(72) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_name` (`user_name`,`password`),
  UNIQUE KEY `user_session` (`user_id`,`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



