<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\Leads\DataTransformer;

class DatimTransformer extends AbstractDateTransformer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->format = $GLOBALS['TL_CONFIG']['datimFormat'];
    }
}
