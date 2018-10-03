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

        return 0 !== Notification::countBy('type', 'core_form');
    }
}
