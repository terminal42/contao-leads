<?php
/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\EventListener;

use Contao\BackendUser;
use Contao\Database;
use Contao\Input;
use Contao\Session;

class UserNavigationListener
{
    /**
     * @var Database
     */
    private $db;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var BackendUser
     */
    private $user;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->db      = Database::getInstance();
        $this->session = Session::getInstance();
        $this->user    = BackendUser::getInstance();
    }

    public function onLoadLanguageFile(string $name): void
    {
        if ('modules' === $name && 'lead' === \Input::get('do')) {
            $objForm = $this->db
                ->prepare("SELECT * FROM tl_form WHERE id=?")
                ->execute(\Input::get('master'))
            ;

            $GLOBALS['TL_LANG']['MOD']['lead'][0] = $objForm->leadMenuLabel ?: $objForm->title;
        }
    }

    /**
     * Add leads to the backend navigation.
     *
     * @param array $modules
     * @param bool  $showAll
     *
     * @return array
     */
    public function onGetUserNavigation(array $modules, $showAll)
    {
        $forms = $this->getForms();

        if (0 === count($forms)) {
            unset($modules['leads']);

            return $modules;
        }

        $isOpen = $showAll || $this->isOpen();

        if ($isOpen) {
            $modules['leads']['modules'] = array();

            foreach ($forms as $form) {
                $modules['leads']['modules']['lead_'. $form['id']] = array(
                    'tables'    => array('tl_lead'),
                    'title'     => specialchars(sprintf($GLOBALS['TL_LANG']['MOD']['leads'][1], $form['title'])),
                    'label'     => $form['leadMenuLabel'],
                    'icon'      => ' style="background-image:url(\'system/modules/leads/assets/icon.png\')"',
                    'class'     => 'navigation leads',
                    'href'      => 'contao/main.php?do=lead&master='.$form['id'],
                    'isActive'  => 'lead' === Input::get('do') && $form['id'] === Input::get('master'),
                );
            }
        } else {
            $modules['leads']['modules'] = false;
            $modules['leads']['icon']    = 'modPlus.gif';
            $modules['leads']['title']   = specialchars($GLOBALS['TL_LANG']['MSC']['expandNode']);
        }

        return $modules;
    }

    /**
     * Gets forms with enabled leads.
     * 
     * @return array
     */
    private function getForms()
    {
        if (!$this->db->tableExists('tl_lead')) {
            return array();
        }

        $allowedIds = $this->getAllowedFormIds();

        if (false === $allowedIds) {
            return array();
        }

        $permission = true === $allowedIds ? '' : sprintf(' AND id IN (%s)', implode(',', $allowedIds));

        // Master forms
        $forms = $this->db->execute("SELECT id, title, leadMenuLabel FROM tl_form WHERE leadEnabled='1' AND leadMaster=0" . $permission)
            ->fetchAllAssoc();

        $ids = array();
        foreach ($forms as $k => $form) {
            // Fallback label
            $forms[$k]['leadMenuLabel'] =  $form['leadMenuLabel'] ?: $form['title'];
            $ids[] = $form['id'];
        }

        // Check for orphan data sets that have no associated form anymore
        $filter = 0 === count($ids) ? '' : sprintf(' WHERE master_id NOT IN (%s)', implode(',', $ids));

        $orphans = $this->db->execute("SELECT DISTINCT master_id AS id, CONCAT('ID ', master_id) AS title, CONCAT('ID ', master_id) AS leadMenuLabel FROM tl_lead" . $filter)
            ->fetchAllAssoc();

        // Only show orphans to admins
        if ($this->user->isAdmin) {
            foreach ($orphans as $orphan) {
                $forms[] = $orphan;
            }
        }

        // Order by leadMenuLabel
        usort($forms, function($a, $b) {
            return $a['leadMenuLabel'] > $b['leadMenuLabel'];
        });

        return $forms;
    }

    /**
     * @return bool|int[]
     */
    private function getAllowedFormIds()
    {
        if ($this->user->isAdmin) {
            return true;
        }

        if (!$this->user->hasAccess('lead', 'modules')
            || !is_array($this->user->forms)
            || 0 === count($this->user->forms)
        ) {
            return false;
        }

        return array_map('intval', $this->user->forms);
    }

    /**
     * @return bool
     */
    private function isOpen()
    {
        if (version_compare(VERSION, '4.4', '>=')) {
            return true;
        }

        $backendModules = $this->session->get('backend_modules');

        return (bool) $backendModules['leads'];
    }
}
