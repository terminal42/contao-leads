<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export\Format;

use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsFormatter;

#[AsLeadsFormatter('yesno')]
class YesnoFormatter implements FormatterInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function format(mixed $value, string $type): string
    {
        return $this->translator->trans('MSC.'.($value ? 'yes' : 'no'), [], 'contao_default');
    }
}
