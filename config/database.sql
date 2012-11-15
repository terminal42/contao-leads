-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_lead`
--

CREATE TABLE `tl_lead` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `master_id` int(10) unsigned NOT NULL default '0',
  `form_id` int(10) unsigned NOT NULL default '0',
  `member_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_lead_data`
--

CREATE TABLE `tl_lead_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `master_id` int(10) unsigned NOT NULL default '0',
  `field_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `value` text NULL,
  `label` text NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_form`
--

CREATE TABLE `tl_form` (
  `leadEnabled` char(1) NOT NULL default '',
  `leadMaster` int(10) unsigned NOT NULL default '0',
  `leadMenuLabel` varchar(32) NOT NULL default '',
  `leadLabel` text NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_form_field`
--

CREATE TABLE `tl_form_field` (
  `leadStore` varchar(10) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

