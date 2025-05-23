<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsCallback('tl_form', 'fields.leadMain.options')]
class FormMainOptionsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function __invoke(DataContainer $dc): array
    {
        $options = [];

        $forms = $this->connection->fetchAllAssociative(
            "SELECT id, title FROM tl_form WHERE leadEnabled='1' AND leadMain=0 AND id!=?",
            [$dc->id],
        );

        foreach ($forms as $form) {
            if (!$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FORM, $form['id'])) {
                continue;
            }

            $options[$form['id']] = $form['title'];
        }

        return $options;
    }
}
