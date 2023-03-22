<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

#[AsCallback('tl_lead_export', 'config.onload')]
class ExportAccessListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly Security $security,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (
            null === $request
            || 'form' !== $request->query->get('do')
            || !$this->scopeMatcher->isBackendRequest($request)
        ) {
            return;
        }

        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'form');
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, 'tl_lead_export');
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_FORM, $this->getFormId());
    }

    protected function denyAccessUnlessGranted(mixed $attribute, mixed $subject = null, string $message = 'Access Denied.'): void
    {
        if (!$this->security->isGranted($attribute, $subject)) {
            $exception = new AccessDeniedException($message);
            $exception->setAttributes($attribute);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    private function getFormId(): int|null
    {
        $id = (int) Input::get('id') ?: null;
        $pid = (int) Input::get('pid') ?: null;
        $act = Input::get('act');
        $mode = Input::get('mode');

        // For these actions the id parameter refers to the parent record
        if (
            ('paste' === $act && 'create' === $mode)
            || \in_array($act, [null, 'select', 'editAll', 'overrideAll', 'deleteAll'], true)
        ) {
            return $id;
        }

        // For these actions the pid parameter refers to the insert position
        if (\in_array($act, ['create', 'cut', 'copy', 'cutAll', 'copyAll'], true)) {
            // Mode “paste into”
            if ('2' === $mode) {
                return $pid;
            }

            // Mode “paste after”
            $id = $pid;
        }

        if (!$id) {
            return null;
        }

        $currentRecord = $this->connection->fetchAssociative('SELECT * FROM tl_lead_export WHERE id=?', [$id]);

        if (!empty($currentRecord['pid'])) {
            return (int) $currentRecord['pid'];
        }

        return null;
    }
}
