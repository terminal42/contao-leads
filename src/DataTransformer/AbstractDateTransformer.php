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

abstract class AbstractDateTransformer extends AbstractTransformer
{
    /**
     * PHP date format.
     */
    protected $format;

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return \Date::parse($this->format, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        try {
            return (new \Date($value, $this->format))->tstamp;
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), 0, $e);
        }
    }
}
