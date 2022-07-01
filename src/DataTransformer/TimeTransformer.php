<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\DataTransformer;

class TimeTransformer extends AbstractDateTransformer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->format = $GLOBALS['TL_CONFIG']['timeFormat'];
    }
}
