--
-- Table structure for table `guild_achievements_cats`
--

CREATE TABLE IF NOT EXISTS `guild_achievements_cats` (
  `id` int(10) unsigned NOT NULL,
  `sub_id` int(10) unsigned NOT NULL,
  `in_order` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`sub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `guild_achievements_data`
--

CREATE TABLE IF NOT EXISTS `guild_achievements_data` (
  `id` int(10) unsigned NOT NULL,
  `player` varchar(255) NOT NULL,
  `when` int(10) unsigned NOT NULL,
  `first` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id`,`player`),
  KEY `id` (`id`,`when`),
  KEY `when` (`when`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `guild_achievements_key`
--

CREATE TABLE IF NOT EXISTS `guild_achievements_key` (
  `id` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `sub_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `points` int(10) unsigned NOT NULL,
  `num_players` int(10) unsigned NOT NULL,
  KEY `id` (`id`),
  KEY `cat_id` (`cat_id`,`sub_id`,`num_players`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `guild_quests_cats`
--

CREATE TABLE IF NOT EXISTS `guild_quests_cats` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `in_order` int(10) unsigned NOT NULL,
  `num_quests` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`name`,`cat_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `guild_quests_data`
--

CREATE TABLE IF NOT EXISTS `guild_quests_data` (
  `id` int(10) unsigned NOT NULL,
  `player` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`player`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `guild_quests_key`
--

CREATE TABLE IF NOT EXISTS `guild_quests_key` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `num_players` int(10) unsigned NOT NULL,
  `have_info` tinyint(3) unsigned NOT NULL,
  `last_fetched` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `have_info` (`have_info`),
  KEY `num_players` (`num_players`),
  KEY `category` (`category`,`num_players`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `guild_stats`
--

CREATE TABLE IF NOT EXISTS `guild_stats` (
  `stat_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `value_i` int(10) unsigned NOT NULL,
  `value_f` double NOT NULL,
  `highest` varchar(255) NOT NULL,
  `last_update` int(10) unsigned NOT NULL,
  `hidden` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`stat_id`,`name`),
  KEY `last_update` (`last_update`),
  KEY `stat_id` (`stat_id`,`highest`,`value_f`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `guild_stats_key`
--

CREATE TABLE IF NOT EXISTS `guild_stats_key` (
  `id` int(10) unsigned NOT NULL,
  `cat` varchar(255) NOT NULL,
  `subcat` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `guild_news`
--

CREATE TABLE IF NOT EXISTS `guild_news` (
  `timestamp` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY  (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

