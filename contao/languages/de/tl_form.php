<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @see       http://github.com/terminal42/contao-leads
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form']['leadEnabled']   = array('Anfragen speichern', 'Daten aus diesem Formular als Anfragen speichern.');
$GLOBALS['TL_LANG']['tl_form']['leadMaster']    = array('Hauptkonfiguration', 'Wählen Sie ob dies eine Hauptkonfiguration oder ein Zweitformular ist.');
$GLOBALS['TL_LANG']['tl_form']['leadMenuLabel'] = array('Navigations-Bezeichnung', 'Geben Sie eine eigene Bezeichnung für den Navigationspunkt im Backend ein. Wenn Sie dieses Feld leer lassen, wir der Formulartitel verwendet.');
$GLOBALS['TL_LANG']['tl_form']['leadLabel']     = array('Datensatz-Bezeichnung', 'Geben Sie die Namen der Felder ein, die in der Backend-Liste angezeigt werden sollen, umgeben von Doppelhashes (##feldname##). Sie können auch normalen Text verwenden. Verwenden Sie ##created## für die Ausgabe des Datums und die Uhrzeit der Erstellung.');
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']    = array('Speicherzeit für Anfragen', 'Hier können Sie die Speicherzeit für Anfragen eingeben. 0 deaktiviert die automatische Löschung.');
$GLOBALS['TL_LANG']['tl_form']['leadPurgeUploads'] = array('Uploads von Anfragen löschen', 'Beim automatischen Löschen von Anfragen sollen auch die Uploads gelöscht werden.');

$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['days'] = 'Tag(e)';
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['weeks'] = 'Woche(n)';
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['months'] = 'Monat(e)';
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['years'] = 'Jahr(e)';

/**
 * Other
 */
$GLOBALS['TL_LANG']['tl_form']['leadMasterBlankOptionLabel'] = 'Dies ist ein Master-Formular';
