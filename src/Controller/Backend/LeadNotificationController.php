<?php

namespace Terminal42\LeadsBundle\Controller\Backend;

use Contao\Controller;
use Contao\Environment;
use Contao\FormModel;
use Contao\Input;
use Contao\Message;
use Contao\System;
use NotificationCenter\Model\Notification;
use Terminal42\LeadsBundle\Util\NotificationCenter;

class LeadNotificationController
{
    /**
     * @var NotificationCenter
     */
    private $notificationCenter;

    public function __construct(NotificationCenter $notificationCenter)
    {
        $this->notificationCenter = $notificationCenter;
    }

    public function __invoke()
    {
        if (\Input::get('master') || !$this->notificationCenter->isAvailable()) {
            // TODO show exception message in the backend
            Controller::redirect('contao/main.php?act=error');
        }

        $notifications = [];
        $notificationsCollection = Notification::findBy('type', 'core_form');

        if (null === $notificationsCollection) {
            // TODO show exception message in the backend
            Controller::redirect('contao/main.php?act=error');
        }

        // Generate the notifications
        foreach ($notificationsCollection as $notification) {
            $notifications[$notification->id] = $notification->title;
        }

        // Process the form
        if ('tl_leads_notification' === Input::post('FORM_SUBMIT')) {
            /**
             * @var \FormModel   $form
             * @var Notification $notification
             */
            if (!isset($notifications[Input::post('notification')])
                || !is_array(\Input::post('IDS'))
                || ($form = \FormModel::findByPk(\Input::get('master'))) === null
                || null === ($notification = Notification::findByPk(\Input::post('notification')))
            ) {
                Controller::reload();
            }

            if (\Input::get('id')) {
                $ids = [(int) \Input::get('id')];
            } else {
                $session = \Session::getInstance()->getData();
                $ids = array_map('intval', $session['CURRENT']['IDS']);
            }

            foreach ($ids as $id) {
                if ($this->send($id, $form, $notification)) {
                    Message::addConfirmation(
                        sprintf($GLOBALS['TL_LANG']['tl_lead']['notification_confirm'], $id)
                    );
                }
            }

            Controller::redirect(\System::getReferer());
        }

        return $this->generateForm($notifications, [\Input::get('id')]);
    }


    /**
     * @param Notification[] $notifications
     * @param int[]                                    $ids
     *
     * @return string
     */
    private function generateForm(array $notifications, array $ids)
    {
        // TODO refactor to use a template
        if (version_compare(VERSION, '4.4', '>=')) {
            $GLOBALS['TL_CSS'][] = 'system/modules/leads/assets/notification-center.css';
        }

        $return = '
<div id="tl_buttons">
<a href="'.System::getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<div class="leads-notification-center">
<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_lead']['notification'][0].'</h2>
'.\Message::generate().'
<form action="'.ampersand(Environment::get('request'), true).'" id="tl_leads_notification" class="tl_form" method="post">
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
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['notification'][0]).'">
</div>

</div>
</form>';

        return $return;
    }

    /**
     * Send lead data using given notification
     *
     * @param int                                    $leadId
     * @param FormModel                             $form
     * @param Notification $notification
     *
     * @return bool
     */
    private function send($leadId, FormModel $form, Notification $notification)
    {
        $result = $notification->send($this->generateTokens($leadId, $form));

        return !in_array(false, $result);
    }

    /**
     * Generate simple tokens for a lead record.
     *
     * @param int       $leadId
     * @param FormModel $form
     *
     * @return array
     */
    private function generateTokens($leadId, FormModel $form)
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

        return $this->generateNotificationCenterTokens($data, $form->row(), $labels);
    }

    /**
     * Generates the NC tokens.
     *
     * @param array $arrData
     * @param array $arrForm
     * @param array $arrLabels
     *
     * @return array
     */
    private function generateNotificationCenterTokens(array $arrData, array $arrForm, array $arrLabels)
    {
        $arrTokens = array();
        $arrTokens['raw_data'] = '';

        foreach ($arrData as $k => $v) {
            \Haste\Util\StringUtil::flatten($v, 'form_'.$k, $arrTokens);
            $arrTokens['formlabel_'.$k] = isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k);
            $arrTokens['raw_data'] .= (isset($arrLabels[$k]) ? $arrLabels[$k] : ucfirst($k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
        }

        foreach ($arrForm as $k => $v) {
            \Haste\Util\StringUtil::flatten($v, 'formconfig_'.$k, $arrTokens);
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        return $arrTokens;
    }
}
