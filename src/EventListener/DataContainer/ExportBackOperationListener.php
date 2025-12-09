<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback('tl_lead_export', 'config.onload')]
class ExportBackOperationListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!($id = $request?->query->getInt('id'))) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_lead_export']['config']['backlink'] = 'do=lead&form='.$id;
    }
}
