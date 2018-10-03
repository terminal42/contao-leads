<?php

namespace Terminal42\LeadsBundle\Util;

use NotificationCenter\Model\Notification;

class NotificationCenter
{
    /**
     * @var array
     */
    private $bundles;

    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    public function isAvailable(): bool
    {
        if (!array_key_exists('notification_center', $this->bundles)) {
            return false;
        }

        return Notification::countBy('type', 'core_form') !== 0;
    }
}
