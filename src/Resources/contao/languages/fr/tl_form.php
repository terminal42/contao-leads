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
$GLOBALS['TL_LANG']['tl_form']['leadEnabled']   = array('Enregistrer les données avec Leads', 'Enregistrer les données de ce formulaire avec Leads.');
$GLOBALS['TL_LANG']['tl_form']['leadMaster']    = array('Configuration dans Leads', 'Déterminer s\'il s\'agit d\'un formulaire principal (langue principale utilisée aussi en BE) ou d\'un formulaire lié (autres langues).');
$GLOBALS['TL_LANG']['tl_form']['leadMenuLabel'] = array('Libellé de navigation en backend', 'Saisissez un libellé personnalisé pour l\'affichage en backend. Laissez vide pour utiliser le nom du formulaire.');
$GLOBALS['TL_LANG']['tl_form']['leadLabel']     = array('Champs de la liste en backend', 'Saisissez les noms des champs à afficher dans la liste BE entourés de doubles dièses (##champs##). Vous pouvez utiliser également du texte normal. Utilisez ##created## pour la date et l\'heure de création.');
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']    = array('Durée de stockage des leads', 'Saisir la durée de stockage des entrées du leads. 0 désactive la suppression automatique.');
$GLOBALS['TL_LANG']['tl_form']['leadPurgeUploads'] = array('Supprimer les téléchargements des leads', 'Lors de la suppression automatique des leads, les téléchargements doivent également être supprimés.');

$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['days'] = 'jour(s)';
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['weeks'] = 'semaine(s)';
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['months'] = 'mois';
$GLOBALS['TL_LANG']['tl_form']['leadPeriod']['options']['years'] = 'année(s)';

/**
 * Other
 */
$GLOBALS['TL_LANG']['tl_form']['leadMasterBlankOptionLabel'] = 'Formulaire principal';
