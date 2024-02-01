<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsHook('initializeSystem')]
#[AsHook('loadLanguageFile')]
#[AsHook('getUserNavigation')]
class UserNavigationListener
{
    private array|null $forms = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly Packages $packages,
    ) {
    }

    /**
     * Load the CSS file for the back end navigation group icon.
     */
    public function onInitializeSystem(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$this->scopeMatcher->isBackendRequest($request)) {
            return;
        }

        $GLOBALS['TL_JAVASCRIPT'][] = $this->packages->getUrl('leads.js', 'terminal42_leads');
        $GLOBALS['TL_CSS'][] = $this->packages->getUrl('leads.css', 'terminal42_leads');
    }

    /**
     * Set the translation to the currently active lead for the breadcrumb.
     */
    public function onLoadLanguageFile(string $name): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (
            'modules' !== $name
            || null === $request
            || !$this->scopeMatcher->isBackendRequest($request)
            || 'lead' !== $request->query->get('do')
        ) {
            return;
        }

        $formId = $request->query->getInt('form');

        foreach ($this->getForms() as $form) {
            if ($form['id'] === $formId) {
                $GLOBALS['TL_LANG']['MOD']['lead'][0] = $form['leadMenuLabel'] ?: StringUtil::decodeEntities($form['title']);
                break;
            }
        }
    }

    /**
     * Add leads to the backend navigation.
     */
    public function onGetUserNavigation(array $modules): array
    {
        $forms = $this->getForms();

        if (empty($forms)) {
            unset($modules['leads']);

            return $modules;
        }

        $request = $this->requestStack->getCurrentRequest();
        $modules['leads']['modules'] = [];

        foreach ($forms as $form) {
            $formTitle = StringUtil::decodeEntities($form['title']);

            $modules['leads']['modules']['lead_'.$form['id']] = [
                'title' => StringUtil::specialchars($this->translator->trans('MOD.leads.1', [$formTitle], 'contao_modules')),
                'label' => $form['leadMenuLabel'] ?: $formTitle,
                'class' => 'navigation leads',
                'href' => $this->urlGenerator->generate('contao_backend', ['do' => 'lead', 'form' => $form['id']]),
                'isActive' => $request && 'lead' === $request->query->get('do') && (int) $form['id'] === $request->query->getInt('form'),
            ];
        }

        return $modules;
    }

    /**
     * Gets main forms with enabled leads.
     */
    private function getForms(): array
    {
        if (null !== $this->forms) {
            return $this->forms;
        }

        if (!$this->connection->createSchemaManager()->tablesExist(['tl_lead'])) {
            return [];
        }

        $forms = $this->connection->fetchAllAssociative("SELECT id, title, leadMenuLabel FROM tl_form WHERE leadEnabled='1' AND leadMain=0");

        if ($this->security->isGranted('ROLE_ADMIN')) {
            // Find lead records where the related form has been deleted
            $forms = array_merge($forms, $this->connection->fetchAllAssociative(
                <<<'SQL'
                        SELECT
                            l.main_id AS id,
                            CONCAT('ID ', l.main_id) AS title,
                            CONCAT('ID ', l.main_id) AS leadMenuLabel
                        FROM tl_lead l
                            LEFT JOIN tl_form f ON l.main_id=f.id
                        WHERE f.id IS NULL
                        GROUP BY l.main_id
                    SQL
            ));
        } else {
            // Remove forms the user does not have access to
            $forms = array_filter(
                $forms,
                fn (array $form) => $this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_FORM, $form['id'])
            );
        }

        usort($forms, static fn (array $a, array $b) => ($a['leadMenuLabel'] ?: $a['title']) <=> $b['leadMenuLabel'] ?: $b['title']);

        return $this->forms = $forms;
    }
}
