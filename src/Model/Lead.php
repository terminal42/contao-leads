<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Model;

use Contao\Model;
use Contao\System;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $master_id
 * @property int    $form_id
 * @property string $language
 * @property int    $created
 * @property int    $member_id
 * @property mixed  $post_data
 */
class Lead extends Model
{
    protected static $strTable = 'tl_lead';

    /**
     * Default system columns.
     */
    public static function getSystemColumns(): array
    {
        System::loadLanguageFile('tl_lead_export');

        return [
            '_form' => [
                'field' => '_form',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_form'],
                'value' => 'all',
                'format' => 'raw',
                'valueColRef' => 'form_id',
                'labelColRef' => 'form_name',
            ],
            '_created' => [
                'field' => '_created',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_created'],
                'value' => 'value',
                'format' => 'datim',
                'valueColRef' => 'created',
            ],
            '_member' => [
                'field' => '_member',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_member'],
                'value' => 'all',
                'format' => 'raw',
                'valueColRef' => 'member_id',
                'labelColRef' => 'member_name',
            ],
            '_skip' => [
                'field' => '_skip',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_skip'],
                'value' => 'value',
                'format' => 'raw',
            ],
        ];
    }
}
