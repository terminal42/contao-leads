<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback('tl_lead', 'config.onload')]
#[AsCallback('tl_lead_data', 'config.onload')]
#[AsCallback('tl_lead_export', 'config.onload')]
class LeadUrlListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        switch ($dc->table) {
            case 'tl_lead':
                $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['filter'][] = ['main_id=?', $this->requestStack->getCurrentRequest()?->query->getInt('form')];
                break;

            case 'tl_lead_data':
                $GLOBALS['TL_DCA']['tl_lead_data']['config']['backlink'] = 'do=lead&form='.$this->connection->fetchOne('SELECT main_id FROM tl_lead WHERE id=?', [$dc->currentPid]);
                break;

            case 'tl_lead_export':
                $GLOBALS['TL_DCA']['tl_lead_export']['config']['backlink'] = 'do=lead&form='.$dc->currentPid;
                break;
        }
    }
}
