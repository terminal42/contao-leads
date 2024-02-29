<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_lead', 'config.onload')]
class ExportOperationsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
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
            $operations[] = [
                'label' => $config['name'],
                'class' => 'leads_export__'.$config['type'],
                'button_callback' => fn ($href, $label, $title, $class, $attributes) => sprintf(
                    '<a href="%s" class="%s" title="%s" %s>%s</a> ',
                    $this->urlGenerator->generate('terminal42_leads_export', ['id' => $config['id']]),
                    $class,
                    StringUtil::specialchars($title),
                    $attributes,
                    $label,
                ),
                'icon' => $this->packages->getUrl('images/export.png', 'terminal42_leads'),
            ];
        }

        if (
            $this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'form')
            && $this->security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, 'tl_lead_export')
        ) {
            $operations[] = [
                'label' => [
                    $this->translator->trans('tl_lead.export_config.0', [], 'contao_tl_lead'),
                    $this->translator->trans('tl_lead.export_config.1', [], 'contao_tl_lead'),
                ],
                'href' => 'do=form&table=tl_lead_export&id='.$formId,
                'icon' => 'modules',
            ];
        }

        array_unshift($GLOBALS['TL_DCA']['tl_lead']['list']['global_operations'], ...$operations);
    }
}
