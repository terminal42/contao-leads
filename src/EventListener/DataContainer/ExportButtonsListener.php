<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\Export\ExporterInterface;

#[AsCallback('tl_lead', 'select.buttons')]
class ExportButtonsListener
{
    /**
     * @param ServiceLocator<ExporterInterface> $exporters
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
        private readonly ServiceLocator $exporters,
    ) {
    }

    public function __invoke(array $buttons): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || !($formId = $request->query->getInt('form'))) {
            return $buttons;
        }

        $exports = $this->connection->iterateAssociative('SELECT * FROM tl_lead_export WHERE pid=? AND tstamp!=0 ORDER BY name', [$formId]);

        foreach ($exports as $config) {
            if ('tl_select' === $request->request->get('FORM_SUBMIT') && $request->request->getBoolean('export_'.$config['id'])) {
                $this->export($config, $request->request->all('IDS'));
            }

            $buttons['export_'.$config['id']] = \sprintf(
                '<button type="submit" name="export_%s" id="export_%s" class="tl_submit" value="1">%s</button>',
                $config['id'],
                $config['id'],
                $this->translator->trans('tl_lead.export.0', [$config['name']], 'contao_tl_lead'),
            );
        }

        return $buttons;
    }

    protected function denyAccessUnlessGranted(mixed $attribute, mixed $subject = null, string $message = 'Access Denied.'): void
    {
        if (!$this->authorizationChecker->isGranted($attribute, $subject)) {
            $exception = new AccessDeniedException($message);
            $exception->setAttributes($attribute);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    private function export(array $config, array $ids): never
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'lead');
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_EDIT_FORM, $config['pid']);

        if (!$this->exporters->has($config['type'])) {
            throw new NotFoundHttpException('Leads export type "'.$config['type'].'" not found.');
        }

        throw new ResponseException($this->exporters->get($config['type'])->getResponse($config, $ids));
    }
}
