<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Doctrine\DBAL\Connection;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_lead', 'config.onload')]
class ExportOperationsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly Packages $packages,
    ) {
    }

    public function __invoke(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || !($formId = $request->query->getInt('form'))) {
            return;
        }

        $operations = [];
        $exports = $this->connection->iterateAssociative('SELECT * FROM tl_lead_export WHERE pid=? AND tstamp!=0 ORDER BY name', [$formId]);

        foreach ($exports as $config) {
            $operations['export_'.$config['id']] = [
                'label' => [$config['name']],
                'button_callback' => function (DataContainerOperation $operation) use ($config): void {
                    $operation->setUrl($this->urlGenerator->generate('terminal42_leads_export', ['id' => $config['id']]));
                },
                'icon' => $this->packages->getUrl('images/export.svg', 'terminal42_leads'),
                'primary' => (bool) ($config['primary'] ?? false),
            ];
        }

        $operations[] = '-';

        if (
            $this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'form')
            && $this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, 'tl_lead_export')
        ) {
            $operations['export_config'] = [
                'label' => [
                    $this->translator->trans('tl_lead.export_config.0', [], 'contao_tl_lead'),
                    $this->translator->trans('tl_lead.export_config.1', [], 'contao_tl_lead'),
                ],
                'href' => 'do=form&table=tl_lead_export&id='.$formId,
                'icon' => 'wrench.svg',
            ];
        }

        $GLOBALS['TL_DCA']['tl_lead']['list']['global_operations'] = $operations + ($GLOBALS['TL_DCA']['tl_lead']['list']['global_operations'] ?? []);
    }
}
