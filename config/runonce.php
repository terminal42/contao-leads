<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class LeadsRunonce extends Controller
{

	protected $Leads;


	public function run()
	{
		$this->import('Database');
		$this->import('Leads');

		// Cancel if no old data is available
		if (!$this->Database->tableExists('tl_leads') || !$this->Database->tableExists('tl_lead_groups') || !$this->Database->tableExists('tl_lead_fields'))
		{
			return;
		}

		// Create tables and fields so we can migrate data
		$this->prepareTables();

		// Update form configuration to show lead forms
		$this->updateFormConfiguration();

		// Migrate existing data to the new tables
		$this->migrateLeadsData();

		// Delete old tables, otherwise the converter might run again
		$this->Database->query("DROP TABLE tl_leads");
		$this->Database->query("DROP TABLE tl_lead_groups");
		$this->Database->query("DROP TABLE tl_lead_fields");
	}


	/**
	 * Create database tables if necessary
	 */
	private function prepareTables()
	{
		if (!$this->Database->tableExists('tl_lead'))
		{
			$this->Database->query("
CREATE TABLE `tl_lead` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `master_id` int(10) unsigned NOT NULL default '0',
  `form_id` int(10) unsigned NOT NULL default '0',
  `member_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		}

		if (!$this->Database->tableExists('tl_lead_data'))
		{
			$this->Database->query("
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
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		}

		if (!$this->Database->fieldExists('leadEnabled', 'tl_form'))
		{
			$this->Database->query("ALTER TABLE tl_form ADD COLUMN `leadEnabled` char(1) NOT NULL default ''");
		}

		if (!$this->Database->fieldExists('leadMaster', 'tl_form'))
		{
			$this->Database->query("ALTER TABLE tl_form ADD COLUMN `leadMaster` int(10) unsigned NOT NULL default '0'");
		}

		if (!$this->Database->fieldExists('leadMenuLabel', 'tl_form'))
		{
			$this->Database->query("ALTER TABLE tl_form ADD COLUMN `leadMenuLabel` varchar(255) NOT NULL default ''");
		}

		if (!$this->Database->fieldExists('leadLabel', 'tl_form'))
		{
			$this->Database->query("ALTER TABLE tl_form ADD COLUMN `leadLabel` text NULL");
		}

		if (!$this->Database->fieldExists('leadStore', 'tl_form_field'))
		{
			$this->Database->query("ALTER TABLE tl_form_field ADD COLUMN `leadStore` varchar(10) NOT NULL default ''");
		}
	}


	private function updateFormConfiguration()
	{
		$this->Database->query("UPDATE tl_form SET
									leadEnabled='1',
									leadMenuLabel=IF((SELECT name FROM tl_lead_groups WHERE id=tl_form.leadGroup) IS NULL, title, CONCAT((SELECT name FROM tl_lead_groups WHERE id=tl_form.leadGroup), ' (', title, ')')),
									leadLabel=IFNULL((SELECT label FROM tl_lead_groups WHERE id=tl_form.leadGroup), '')
								WHERE leadGroup>0");

		$this->Database->query("UPDATE tl_form_field SET leadStore='1' WHERE leadField!=''");
	}


	private function migrateLeadsData()
	{
		list($arrForms, $arrFields) = $this->getFormConfiguration();

		$objLeads = $this->Database->query("SELECT * FROM tl_leads");

		while ($objLeads->next())
		{
			if (!isset($arrForms[$objLeads->form_id]) || empty($arrFields[$objLeads->form_id]))
			{
				continue;
			}

			$arrData = array();

			foreach ($arrFields[$objLeads->form_id] as $source => $target)
			{
				$arrData[$target] = $objLeads->$source;
			}

			$this->Leads->processFormData($arrData, $arrForms[$objLeads->form_id], array());
		}
	}


	private function getFormConfiguration()
	{
		$arrForms = array();
		$arrFields = array();

		$objForms = $this->Database->query("SELECT * FROM tl_form WHERE leadGroup>0");

		while ($objForms->next())
		{
			$arrForms[$objForms->id] = $objForms->row();
			$arrFields[$objForms->id] = array();

			$objFields = $this->Database->prepare("SELECT name, leadField FROM tl_form_field WHERE pid=? AND leadField!=''")->execute($objForms->id);

			while ($objFields->next())
			{
				$arrFields[$objForms->id][$objFields->leadField] = $objFields->name;
			}
		}

		return array($arrForms, $arrFields);
	}
}


$objLeadsRunonce = new LeadsRunonce();
$objLeadsRunonce->run();

