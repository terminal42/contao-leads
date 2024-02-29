<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class LeadSearchListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Connection $connection,
    ) {
    }

    #[AsCallback('tl_lead', 'config.onload')]
    public function applySearch(): void
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $session = $sessionBag->all();

        if (empty($session['search']['tl_lead']['value'])) {
            return;
        }

        $ids = $this->connection->fetchFirstColumn(
            <<<'SQL'
                    SELECT DISTINCT l.id
                    FROM tl_lead l
                    JOIN tl_lead_data d ON l.id=d.pid
                    WHERE d.value REGEXP ? OR d.label REGEXP ?
                SQL,
            [$session['search']['tl_lead']['value'], $session['search']['tl_lead']['value']],
        );

        $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root'] = empty($ids) ? [0] : $ids;
    }

    #[AsCallback('tl_lead', 'list.sorting.panel_callback.data_search')]
    public function searchMenu(): string
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $session = $sessionBag->all();

        $request = $this->requestStack->getCurrentRequest();

        if (
            $request
            && $request->isMethod(Request::METHOD_POST)
            && 'tl_filters' === $request->request->get('FORM_SUBMIT')
        ) {
            $session['search']['tl_lead']['value'] = $request->request->get('tl_value');
            $sessionBag->replace($session);
        }

        $active = isset($session['search']['tl_lead']['value']) && '' !== (string) $session['search']['tl_lead']['value'];

        return '
<div class="tl_search tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['search'].':</strong>
<input type="search" name="tl_value" class="tl_text'.($active ? ' active' : '').'" value="'.StringUtil::specialchars($session['search']['tl_lead']['value'] ?? '').'">
</div>';
    }
}
