<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\Export\ExporterInterface;

#[AsCallback('tl_lead_export', 'fields.type.options')]
class ExportTypeOptionsListener
{
    /**
     * @param ServiceLocator<ExporterInterface> $exporters
     */
    public function __construct(
        private readonly ServiceLocator $exporters,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): array
    {
        $options = [];
        $types = array_keys($this->exporters->getProvidedServices());

        foreach ($types as $type) {
            $options[$type] = $this->translator->trans('tl_lead_export.type.'.$type, [], 'contao_tl_lead_export');
        }

        return $options;
    }
}
