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

namespace Terminal42\LeadsBundle\DataTransformer;

abstract class AbstractTransformer implements DataTransformerInterface
{
    public function getType(): string
    {
        $className = \get_called_class();
        $className = substr($className, strrpos($className, '\\') + 1);

        if ('Transformer' === substr($className, -11)) {
            $className = substr($className, 0, -11);
        }

        return lcfirst($className);
    }

    public function getLabel(): string
    {
        return $GLOBALS['TL_LANG']['tl_lead_export']['fields_format'][$this->getType()] ?? $this->getType();
    }
}
