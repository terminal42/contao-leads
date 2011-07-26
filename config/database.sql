-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- 
-- Table `tl_leads`
-- 

CREATE TABLE `tl_leads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `form_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_lead_groups`
-- 

CREATE TABLE `tl_lead_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `label` text NULL,
  `fields` blob NULL,
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_lead_fields`
-- 

CREATE TABLE `tl_lead_fields` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `field_name` varchar(32) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `options` blob NULL,
  `mandatory` char(1) NOT NULL default '',
  `multiple` char(1) NOT NULL default '',
  `size` smallint(5) unsigned NOT NULL default '0',
  `extensions` varchar(255) NOT NULL default '',
  `filter` char(1) NOT NULL default '',
  `search` char(1) NOT NULL default '',
  `foreignKey` varchar(64) NOT NULL default '',
  `rte` varchar(255) NOT NULL default '',
  `maxlength` int(10) unsigned NOT NULL default '0',
  `rgxp` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_form`
-- 

CREATE TABLE `tl_form` (
  `leadGroup` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_form_field`
-- 

CREATE TABLE `tl_form_field` (
  `leadField` varchar(32) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- LEAD FIELDS START --
-- LEAD FIELDS STOP --



