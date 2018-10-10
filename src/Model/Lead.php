<?php

namespace Terminal42\LeadsBundle\Model;

use Contao\Model;

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
}
