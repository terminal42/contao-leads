<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export\Format;

use Contao\Config;
use Contao\Date;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsFormatter;

#[AsLeadsFormatter('date')]
#[AsLeadsFormatter('time')]
#[AsLeadsFormatter('datim')]
class DateFormatter implements FormatterInterface
{
    public function format(mixed $value, string $type): int|string
    {
        return Date::parse(Config::get($type.'Format'), $value);
    }
}
