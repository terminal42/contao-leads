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

class YesnoTransformer extends AbstractTransformer
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     * An example might be transforming a unix timestamp to a human readable date format.
     *
     * @param mixed $value The value in the original representation
     *
     * @throws TransformationFailedException when the transformation fails
     *
     * @return mixed The value in the transformed representation
     */
    public function transform($value)
    {
        if ('1' === $value) {
            return $GLOBALS['TL_LANG']['MSC']['yes'];
        }

        return $GLOBALS['TL_LANG']['MSC']['no'];
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     * An example might be transforming a human readable date format to a unix timestamp.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @throws TransformationFailedException when the transformation fails
     *
     * @return mixed The value in the original representation
     */
    public function reverseTransform($value)
    {
        if ($value === $GLOBALS['TL_LANG']['MSC']['yes']) {
            return '1';
        }

        return '';
    }
}
