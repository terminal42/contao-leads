<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export\Format;

interface FormatterInterface
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     * An example might be transforming a unix timestamp to a human-readable date format.
     *
     * @throws RuntimeException when the transformation fails
     */
    public function format(mixed $value, string $type): int|string;
}
