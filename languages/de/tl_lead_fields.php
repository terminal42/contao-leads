<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @copyright  Andreas Schempp 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_lead_fields']['name']					= array('Name', 'Bitte geben Sie einen Namen für dieses Feld ein.');
$GLOBALS['TL_LANG']['tl_lead_fields']['field_name']				= array('Interner Name', 'Der interne Name entspricht dem Datenbankfeld und muss eindeutig sein.');
$GLOBALS['TL_LANG']['tl_lead_fields']['type']					= array('Feldtyp', 'Bitte wählen Sie einen Feldtyp.');
$GLOBALS['TL_LANG']['tl_lead_fields']['description']			= array('Beschreibung', 'Die Beschreibung wird dem Backend-Benutzer als Hinweis angezeigt.');
$GLOBALS['TL_LANG']['tl_lead_fields']['options']				= array('Optionen', 'Wenn JavaScript deaktiviert ist, speichern Sie unbedingt Ihre Änderungen, bevor Sie die Reihenfolge ändern.');
$GLOBALS['TL_LANG']['tl_lead_fields']['mandatory']				= array('Pflichtfeld', 'Das Feld muss ausgefüllt werden.');
$GLOBALS['TL_LANG']['tl_lead_fields']['multiple']				= array('Mehrfachauswahl', 'Erlaubt die Auswahl mehrerer Optionen.');
$GLOBALS['TL_LANG']['tl_lead_fields']['size']					= array('Listengröße', 'Hier können Sie die Größe der Auswahlliste eingeben..');
$GLOBALS['TL_LANG']['tl_lead_fields']['extensions']				= array('Erlaubte Dateitypen', 'Eine kommagetrennte Liste gültiger Dateiendungen.');
$GLOBALS['TL_LANG']['tl_lead_fields']['rte']					= array('HTML Editor verwenden', 'Wählen Sie eine tinyMCE-Konfigurationsdatei um den erweiterten Editor zu aktivieren.');
$GLOBALS['TL_LANG']['tl_lead_fields']['rgxp']					= array('Eingabeprüfung', 'Die Eingaben anhand eines regulären Ausdrucks prüfen.');
$GLOBALS['TL_LANG']['tl_lead_fields']['maxlength']				= array('Maximale Eingabelänge', 'Hier können Sie die maximale Anzahl an Zeichen (Text) bzw. Bytes (Datei-Uploads) festlegen.');
$GLOBALS['TL_LANG']['tl_lead_fields']['foreignKey']				= array('Fremd-Table & -Feld', 'Anstatt alle Optionen von Hand einzugeben können Sie diese aus einer Datenbank-Tabelle holen.');
$GLOBALS['TL_LANG']['tl_lead_fields']['filter']   				= array('Filter aktivieren', 'Klicken Sie hier um den Backend-Filter für dieses Feld zu aktivieren.');
$GLOBALS['TL_LANG']['tl_lead_fields']['search']		   			= array('Suche aktivieren', 'Klicken Sie hier um die Backend-Suche für diesees Feld zu aktivieren.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_lead_fields']['new']			= array('Neues Feld', 'Ein neues Anfrage-Feld hinzufügen');
$GLOBALS['TL_LANG']['tl_lead_fields']['edit']			= array('Feld bearbeiten', 'Feld ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_lead_fields']['delete']			= array('Feld löschen', 'Feld ID %s löschen');
$GLOBALS['TL_LANG']['tl_lead_fields']['show']			= array('Felddetails', 'Details des Feld ID %s anzeigen');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_lead_fields']['field_legend']		= 'Feldkonfiguration';
$GLOBALS['TL_LANG']['tl_lead_fields']['description_legend']	= 'Beschreibung';
$GLOBALS['TL_LANG']['tl_lead_fields']['options_legend']		= 'Optionen';
$GLOBALS['TL_LANG']['tl_lead_fields']['config_legend']		= 'Einstellungen';

