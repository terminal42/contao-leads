<?php

declare(strict_types=1);

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
        if (!\array_key_exists('notification_center', $this->bundles)) {
            return false;
        }

        return 0 !== Notification::countBy('type', 'core_form');
    }
}
