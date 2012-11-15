-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

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

