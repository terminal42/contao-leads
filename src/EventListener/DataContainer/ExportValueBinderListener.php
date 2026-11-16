<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DataContainer;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Terminal42\LeadsBundle\Export\ExporterInterface;
use Terminal42\LeadsBundle\Export\PhpSpreadsheetExporter;

class ExportValueBinderListener
{
    /**
     * @param ServiceLocator<ExporterInterface> $exporters
     * @param ServiceLocator<IValueBinder>      $valueBinders
     */
    public function __construct(
        private readonly ServiceLocator $exporters,
        private readonly ServiceLocator $valueBinders,
    ) {
    }

    #[AsCallback('tl_lead_export', 'fields.fields.attributes')]
    #[AsCallback('tl_lead_export', 'fields.tokenFields.attributes')]
    public function getAttributes(array $attributes, DataContainer|null $dc = null): array
    {
        try {
            $type = $dc?->getCurrentRecord()['type'] ?? null;
        } catch (AccessDeniedException) {
            return $attributes;
        }

        if (
            null !== $type
            && (
                !$this->exporters->has($type)
                || !$this->exporters->get($type) instanceof PhpSpreadsheetExporter
            )
        ) {
            unset($attributes['fields']['valueBinder']);
        }

        return $attributes;
    }

    #[AsCallback('tl_lead_export', 'fields.fields.fields.valueBinder.options')]
    #[AsCallback('tl_lead_export', 'fields.tokenFields.fields.valueBinder.options')]
    public function getOptions(): array
    {
        return array_keys($this->valueBinders->getProvidedServices());
    }
}
