<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */


/**
 * Load forms language
 */
\System::loadLanguageFile('tl_form');
\System::loadLanguageFile('tl_form_field');


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_lead_export']['name']                       = array('Konfigurationsname', 'Bitte geben Sie hier einen Konfigurationsnamen ein.');
$GLOBALS['TL_LANG']['tl_lead_export']['type']                       = array('Datentyp', 'Bitte wählen Sie einen Datentyp für den Export.');
$GLOBALS['TL_LANG']['tl_lead_export']['filename']                   = array('Individueller Dateiname', 'Hier können Sie einen individuellen Dateinamen vergeben. Sie können Platzhalter verwenden (z.B. ##date##). Siehe Help-Wizard für Details.');
$GLOBALS['TL_LANG']['tl_lead_export']['useTemplate']                = array('Template verwenden', 'Aktivieren Sie diese Checkbox, wenn Sie Ihre Daten in ein Template exportieren möchten.');
$GLOBALS['TL_LANG']['tl_lead_export']['startIndex']                 = array('Start-Index', 'Hier können Sie definieren, auf welcher Zeile leads die Daten einfügen soll. Starten Sie bei 1 (nicht 0)!');
$GLOBALS['TL_LANG']['tl_lead_export']['template']                   = array('Template', 'Wählen Sie hier Ihr Template.');
$GLOBALS['TL_LANG']['tl_lead_export']['headerFields']               = array('Kopfzeile anzeigen', 'Wählen Sie hier ob die Kopfzeile ebenfalls exportiert werden soll.');
$GLOBALS['TL_LANG']['tl_lead_export']['export']                     = array('Export-Typ', 'Bitte wählen Sie hier, welche Daten exportiert werden sollen.');
$GLOBALS['TL_LANG']['tl_lead_export']['fields']                     = array('Felder', 'Bitte wählen Sie, welche Felder exportiert werden sollen.');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_field']               = array('Feld');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_name']                = array('Bezeichnung für Kopfzeile');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']               = array('Wert');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']              = array('Format');
$GLOBALS['TL_LANG']['tl_lead_export']['tokenFields']                = array('Spalten', 'Bitte definieren Sie Ihre Export-Konfiguration.');
$GLOBALS['TL_LANG']['tl_lead_export']['tokenFields_targetColumn']   = array('Spalte');
$GLOBALS['TL_LANG']['tl_lead_export']['tokenFields_tokensValue']    = array('Simple Tokens (und Insert Tags)');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_lead_export']['name_legend']   = 'Name und Datentyp';
$GLOBALS['TL_LANG']['tl_lead_export']['config_legend'] = 'Konfiguration';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_lead_export']['export']['all']          = 'Alle Daten exportieren';
$GLOBALS['TL_LANG']['tl_lead_export']['export']['fields']       = 'Individueller Export';
$GLOBALS['TL_LANG']['tl_lead_export']['export']['tokens']       = 'Individueller Export mit Simple Tokens';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['all']    = 'Bezeichnung und Wert';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['label']  = 'Nur die Bezeichnung (wenn vorhanden, Fallback auf Wert)';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['value']  = 'Nur den Wert';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['raw']   = &$GLOBALS['TL_LANG']['tl_form']['raw'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['date']  = &$GLOBALS['TL_LANG']['tl_form_field']['date'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['datim'] = &$GLOBALS['TL_LANG']['tl_form_field']['datim'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['time']  = &$GLOBALS['TL_LANG']['tl_form_field']['time'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['field_form']             = 'Formular';
$GLOBALS['TL_LANG']['tl_lead_export']['field_created']          = 'Erstellungsdatum';
$GLOBALS['TL_LANG']['tl_lead_export']['field_member']           = 'Mitglied';
$GLOBALS['TL_LANG']['tl_lead_export']['field_skip']             = 'Spalte überspringen';



/**
 * Export types
 */
$GLOBALS['TL_LANG']['tl_lead_export']['type']['csv']  = 'CSV (.csv)';
$GLOBALS['TL_LANG']['tl_lead_export']['type']['xls']  = 'Excel 97/2000/2003 (.xls)';
$GLOBALS['TL_LANG']['tl_lead_export']['type']['xlsx'] = 'Excel 2007/2010 (.xlsx)';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_lead_export']['new']    = array('Neue Konfiguration', 'Erstellen Sie eine neue Konfiguration');
$GLOBALS['TL_LANG']['tl_lead_export']['show']   = array('Konfigurationsdetails', 'Details der Konfiguration mit ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_lead_export']['edit']   = array('Konfiguration editieren', 'Konfiguration ID %s editieren');
$GLOBALS['TL_LANG']['tl_lead_export']['cut']    = array('Konfiguration verschieben', 'Konfiguration ID %s verschieben');
$GLOBALS['TL_LANG']['tl_lead_export']['copy']   = array('Konfiguration duplizieren', 'Konfiguration ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_lead_export']['delete'] = array('Konfiguration löschen', 'Konfiguration ID %s löschen');
