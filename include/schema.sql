--
-- Table structure for table `{PREFIX}_comments`
--

CREATE TABLE `{PREFIX}_comments` (
  `comment_id` int(10) unsigned NOT NULL auto_increment,
  `post_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `comment_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  `ip_address` varchar(15) NOT NULL default '',
  `status` enum('APPROVED','UNAPPROVED','SPAM') NOT NULL default 'APPROVED',
  `linkback` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`comment_id`),
  KEY `post_date_status` (`post_id`,`status`,`comment_date`),
  KEY `status_date` (`status`,`comment_date`),
  KEY `comment_date` (`comment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `{PREFIX}_pages`
--

CREATE TABLE `{PREFIX}_pages` (
  `page_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `body` text NOT NULL,
  `nav_label` varchar(30) NOT NULL default '',
  `uri` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`page_id`),
  KEY `nav_label` (`nav_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `{PREFIX}_posts`
--

CREATE TABLE `{PREFIX}_posts` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `subject` varchar(100) NOT NULL default '',
  `body` text NOT NULL,
  `post_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(10) unsigned NOT NULL default '0',
  `uri` varchar(100) NOT NULL default '',
  `allow_comments` tinyint(1) NOT NULL default '1',
  `published` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  UNIQUE KEY `uri_date` (`uri`,`post_date`),
  KEY `post_date` (`post_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `{PREFIX}_uri_lookup`
--

CREATE TABLE `{PREFIX}_uri_lookup` (
  `uri` varchar(100) NOT NULL default '',
  `type` enum('page', 'post') NOT NULL default 'post',
  `object_id` int(10) unsigned NOT NULL,
  `current` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`uri`),
  KEY `object_uri` (`object_id`, `type`, `uri`),
  KEY `object_current` (`object_id`, `type`, `current`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `{PREFIX}_settings`
--

CREATE TABLE `{PREFIX}_settings` (
  `name` varchar(255) NOT NULL default '',
  `type` enum('V','S') NOT NULL default 'V',
  `data` text NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `{PREFIX}_tags`
--

CREATE TABLE `{PREFIX}_tags` (
  `post_id` int(10) unsigned NOT NULL default '0',
  `tag` varchar(20) default NULL,
  UNIQUE KEY `post_id` (`post_id`,`tag`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `{PREFIX}_users`
--

CREATE TABLE `{PREFIX}_users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

