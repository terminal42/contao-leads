<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsCallback('tl_lead', 'config.onload')]
#[AsCallback('tl_lead_data', 'config.onload')]
class LeadAccessListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly Packages $packages,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (
            null === $request
            || 'lead' !== $request->query->get('do')
            || !$this->scopeMatcher->isBackendRequest($request)
        ) {
            return;
        }

        // Also check permission to view lead data
        if ('tl_lead_data' === $request->query->get('table')) {
            if (!($act = $request->query->get('act')) || 'select' === $act) {
                $formId = (int) $this->connection->fetchOne(
                    'SELECT main_id FROM tl_lead WHERE id=?',
                    [$dc->id],
                );
            } else {
                $formId = (int) $this->connection->fetchOne(
                    'SELECT main_id FROM tl_lead WHERE id=(SELECT pid FROM tl_lead_data WHERE id=?)',
                    [$dc->id],
                );
            }
        } else {
            $formId = $request->query->getInt('form');
        }

        if (!$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FORM, $formId)) {
            $exception = new AccessDeniedException('Not enough permissions to access leads ID "'.$formId.'"');
            $exception->setAttributes(ContaoCorePermissions::USER_CAN_EDIT_FORM);
            $exception->setSubject($formId);

            throw $exception;
        }

        $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['filter'][] = ['main_id=?', $formId];
        $GLOBALS['TL_DCA']['tl_lead']['list']['operations']['data'] = [
            'href' => 'table=tl_lead_data',
            'icon' => $this->packages->getUrl('images/field.png', 'terminal42_leads'),
        ];
    }
}
