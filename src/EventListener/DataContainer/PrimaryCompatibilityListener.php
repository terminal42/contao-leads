<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('loadDataContainer')]
class PrimaryCompatibilityListener
{
    public function __invoke(string $table): void
    {
        if ('tl_lead_export' !== $table || version_compare(ContaoCoreBundle::getVersion(), '5.6', '>=')) {
            return;
        }

        unset($GLOBALS['TL_DCA']['tl_lead_export']['fields']['primary']);
        $GLOBALS['TL_DCA']['tl_lead_export']['fields']['name']['eval']['tl_class'] = 'w50';
    }
}
