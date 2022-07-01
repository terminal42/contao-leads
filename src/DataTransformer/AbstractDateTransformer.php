<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\DataTransformer;

use Contao\Date;

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
        return Date::parse($this->format, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        try {
            return (new Date($value, $this->format))->tstamp;
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), 0, $e);
        }
    }
}
