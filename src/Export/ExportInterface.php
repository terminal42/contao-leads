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

namespace Terminal42\LeadsBundle\Export;

use Contao\File;

interface ExportInterface
{
    public function getType(): string;

    public function getLabel(): string;

    public function isAvailable(): bool;

    public function export(\stdClass $config, $ids = null): File;
}
