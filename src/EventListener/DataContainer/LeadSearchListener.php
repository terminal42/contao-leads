<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Twig\Environment;

class LeadSearchListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Connection $connection,
        private readonly Environment $twig,
    ) {
    }

    #[AsCallback('tl_lead', 'config.onload')]
    public function applySearch(DataContainer $dc): void
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $session = $sessionBag->all();

        if (empty($session['search']['tl_lead']['value'])) {
            return;
        }

        $searchValue = $session['search']['tl_lead']['value'];
        $dc->setPanelState(true);

        try {
            $this->connection->executeStatement("SELECT '' REGEXP ?", [$searchValue]);
        } catch (DriverException) {
            // Quote search string if it is not a valid regular expression
            $searchValue = preg_quote((string) $searchValue);
        }

        $ids = $this->connection->fetchFirstColumn(
            <<<'SQL'
                    SELECT DISTINCT l.id
                    FROM tl_lead l
                    JOIN tl_lead_data d ON l.id=d.pid
                    WHERE d.value REGEXP ? OR d.label REGEXP ?
                SQL,
            [$searchValue, $searchValue],
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

        return $this->twig->render('@Contao/backend/lead_data_search.html.twig', [
            'value' => $session['search']['tl_lead']['value'] ?? '',
        ]);
    }
}
