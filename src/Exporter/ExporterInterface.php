<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Exporter;

use Contao\File;

interface ExporterInterface {

    /**
     * Returns true if available.
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Exports a given set of data row ids using a given configuration.
     *
     * @param \Database\Result $config
     * @param array|null       $ids
     *
     * @return File
     */
    public function export($config, $ids = null);
}
