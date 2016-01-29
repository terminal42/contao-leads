<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\Leads;

class LeadsNotification
{

    /**
     * Return true if the notification_center is installed and can be supported
     *
     * @param bool $checkOptions Enable to check for available form notifications
     *
     * @return bool
     */
    public static function available($checkOptions = false)
    {
        $result = in_array('notification_center', \ModuleLoader::getActive());

        if ($result
            && $checkOptions
            && \NotificationCenter\Model\Notification::countBy('type', 'core_form') === 0
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * Send lead data using given notification
     *
     * @param int                                    $leadId
     * @param \FormModel                             $form
     * @param \NotificationCenter\Model\Notification $notification
     *
     * @return bool
     */
    public static function send($leadId, \FormModel $form, \NotificationCenter\Model\Notification $notification)
    {
        $data   = array();
        $labels = array();

        $leadDataCollection = \Database::getInstance()->prepare("
            SELECT
                name,
                value,
                (SELECT label FROM tl_form_field WHERE tl_form_field.id=tl_lead_data.field_id) AS fieldLabel
            FROM tl_lead_data
            WHERE pid=?
        ")->execute($leadId);

        // Generate the form data and labels
        while ($leadDataCollection->next()) {
            $data[$leadDataCollection->name]   = $leadDataCollection->value;
            $labels[$leadDataCollection->name] = $leadDataCollection->fieldLabel ?: $leadDataCollection->name;
        }

        $formHelper = new \NotificationCenter\tl_form();

        // Send the notification
        $result = $notification->send($formHelper->generateTokens($data, $form->row(), array(), $labels));

        return !in_array(false, $result);
    }


    /**
     * @param \NotificationCenter\Model\Notification[] $notifications
     * @param int[]                                    $ids
     *
     * @return string
     */
    public static function generateForm(array $notifications, array $ids)
    {
        $return = '
<div id="tl_buttons">
<a href="'.\System::getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_lead']['notification'][0].'</h2>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_leads_notification" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_leads_notification">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<input type="hidden" name="IDS[]" value="' . implode('">
<input type="hidden" name="IDS[]" value="', $ids) . '">

<div class="tl_tbox">
  <h3><label for="notification">'.$GLOBALS['TL_LANG']['tl_lead']['notification_list'][0].'</label></h3>
  <select name="notification" id="notification" class="tl_select">';

        // Generate options
        foreach ($notifications as $id => $name) {
            $return .= '<option value="' . $id . '">' . $name . '</option>';
        }

        $return .= '
  </select>
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_lead']['notification_list'][1].'</p>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['notification'][0]).'">
</div>

</div>
</form>';

        return $return;
    }
}
