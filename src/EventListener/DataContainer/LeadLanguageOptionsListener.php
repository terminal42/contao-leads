<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Intl\Locales;

#[AsCallback('tl_lead', 'fields.language.options')]
class LeadLanguageOptionsListener
{
    public function __construct(
        private readonly Locales $locales,
    ) {
    }

    public function __invoke(): array
    {
        return $this->locales->getLocales();
    }
}
