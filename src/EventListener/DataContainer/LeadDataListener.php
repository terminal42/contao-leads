<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;

class LeadDataListener
{
    /**
     * Check permissions to edit table.
     */
    public function onLoadCallback(): void
    {
        $objUser = \BackendUser::getInstance();

        if ($objUser->isAdmin) {
            return;
        }

        $objUser->forms = StringUtil::deserialize($objUser->forms);

        if (!\is_array($objUser->forms) || empty($objUser->forms)) {
            System::log('Not enough permissions to access leads data ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
            Controller::redirect('contao/main.php?act=error');
        }

        $objLeads = \Database::getInstance()->prepare('SELECT master_id FROM tl_lead WHERE id=?')
                             ->limit(1)
                             ->execute(\Input::get('id'))
        ;

        if (!$objLeads->numRows || !\in_array($objLeads->master_id, $objUser->forms, true)) {
            System::log('Not enough permissions to access leads data ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
            Controller::redirect('contao/main.php?act=error');
        }
    }

    /**
     * Add an image to each record.
     *
     * @param array
     * @param string
     *
     * @return string
     */
    public function onChildRecordCallback($row)
    {
        $label = implode(', ', StringUtil::deserialize($row['label'], true));
        $value = implode(', ', StringUtil::deserialize($row['value'], true));

        if ($label === $value) {
            $value = '';
        }

        return sprintf(
            '
<div style="float:left;width:20%%;margin-right:10px;font-weight:500">%s</div>
<div style="float:left;width:50%%;margin-right:10px">%s</div>
<div style="float:left;width:20%%;color:#b3b3b3;">%s</div>',
            $row['name'],
            $label,
            $value
        );
    }
}
