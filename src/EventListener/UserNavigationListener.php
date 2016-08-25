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

        $permission = true === $allowedIds ? '' : sprintf(' AND f.id IN (%s)', implode(',', $allowedIds));

        $result = $this->db->execute("
                SELECT f.id, f.title, IF(f.leadMenuLabel='', f.title, f.leadMenuLabel) AS leadMenuLabel
                FROM tl_form f
                LEFT JOIN tl_lead l ON l.master_id=f.id
                WHERE leadEnabled='1' AND leadMaster=0" . $permission . "
            UNION
                SELECT l.master_id AS id, IFNULL(f.title, CONCAT('ID ', l.master_id)) AS title, IFNULL(IF(f.leadMenuLabel='', f.title, f.leadMenuLabel), CONCAT('ID ', l.master_id)) AS leadMenuLabel
                FROM tl_lead l
                LEFT JOIN tl_form f ON l.master_id=f.id
                WHERE ISNULL(f.id)
                ORDER BY leadMenuLabel
        ");

        return $result->fetchAllAssoc();
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
        $backendModules = $this->session->get('backend_modules');

        return (bool) $backendModules['leads'];
    }
}
