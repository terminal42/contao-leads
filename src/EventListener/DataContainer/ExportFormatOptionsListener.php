<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\Export\Format\FormatterInterface;

#[AsCallback('tl_lead_export', 'fields.fields.eval.columnFields.format.options')]
class ExportFormatOptionsListener
{
    /**
     * @param ServiceLocator<FormatterInterface> $formatters
     */
    public function __construct(
        private readonly ServiceLocator $formatters,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): array
    {
        $options = [];
        $types = array_keys($this->formatters->getProvidedServices());

        foreach ($types as $type) {
            $options[$type] = $this->translator->trans('tl_lead_export.format.'.$type, [], 'contao_tl_lead_export');
        }

        return $options;
    }
}
