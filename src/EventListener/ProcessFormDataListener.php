<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Date;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Terminal42\LeadsBundle\Event\FormDataLabelEvent;
use Terminal42\LeadsBundle\Event\FormDataValueEvent;
use Terminal42\LeadsBundle\Event\LeadsSaveEvent;

#[AsHook('processFormData')]
class ProcessFormDataListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(array $postData, array $formConfig, array|null $files): void
    {
        if (!$formConfig['leadEnabled']) {
            return;
        }

        $leadId = $this->saveLead($postData, $formConfig);
        $fields = $this->getFormFields((int) $formConfig['id'], (int) $formConfig['leadMain']);

        foreach ($fields as $field) {
            $this->saveFormField($leadId, $field, $postData, $files);
        }

        $this->eventDispatcher->dispatch(new LeadsSaveEvent($leadId, $postData, $formConfig, $files), LeadsSaveEvent::NAME);

    }

    private function saveLead(array $postData, array $formConfig): int
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->tokenStorage->getToken()?->getUser();

        $this->connection->insert('tl_lead', [
            'tstamp' => time(),
            'main_id' => $formConfig['leadMain'] ?: $formConfig['id'],
            'form_id' => $formConfig['id'],
            'language' => (string) $request?->getLocale(),
            'created' => time(),
            'member_id' => $user instanceof FrontendUser ? $user->id : 0,
            'post_data' => serialize($postData),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function getFormFields(int $formId, int $mainId): array
    {
        if ($mainId > 0) {
            return $this->connection->fetchAllAssociative(
                <<<'SQL'
                        SELECT
                            main_field.*,
                            form_field.id AS field_id,
                            form_field.name AS postName
                        FROM tl_form_field form_field
                            LEFT JOIN tl_form_field main_field ON form_field.leadStore=main_field.id
                        WHERE
                            form_field.pid=?
                          AND main_field.pid=?
                          AND form_field.leadStore>0
                          AND main_field.leadStore='1'
                          AND form_field.invisible=''
                        ORDER BY main_field.sorting;
                    SQL,
                [$formId, $mainId],
            );
        }

        return $this->connection->fetchAllAssociative(
            <<<'SQL'
                    SELECT
                        *,
                        id AS field_id,
                        name AS postName
                    FROM tl_form_field
                    WHERE pid=?
                      AND leadStore='1'
                      AND invisible=''
                    ORDER BY sorting
                SQL,
            [$formId],
        );
    }

    private function saveFormField(int $leadId, array $field, array $postData, array|null $files): void
    {
        $value = null;

        if (null !== $files && isset($files[$field['postName']]) && ($files[$field['postName']]['uploaded'] ?? false)) {
            $value = $this->prepareValue($files[$field['postName']], $field);
        } elseif (isset($postData[$field['postName']])) {
            $value = $this->prepareValue($postData[$field['postName']], $field);
        }

        if (null !== $value) {
            $label = $this->prepareLabel($value, $field);

            $data = [
                'pid' => $leadId,
                'sorting' => $field['sorting'],
                'tstamp' => time(),
                'main_id' => $field['id'],
                'field_id' => $field['field_id'],
                'name' => $field['name'],
                'value' => \is_scalar($value) ? $value : serialize($value),
                'label' => \is_scalar($label) ? $label : serialize($label),
            ];

            $this->connection->insert('tl_lead_data', $data);
        }
    }

    private function prepareValue(mixed $value, array $field): mixed
    {
        $event = $this->eventDispatcher->dispatch(new FormDataValueEvent($value, $field));

        if ($event->isPropagationStopped()) {
            return $event->getValue();
        }

        if (isset($value['uuid']) && Validator::isUuid($value['uuid'])) {
            return Validator::isBinaryUuid($value['uuid']) ? StringUtil::binToUuid($value['uuid']) : $value['uuid'];
        }

        if (\is_array($value)) {
            return array_map(fn ($v) => $this->prepareValue($v, $field), $value);
        }

        if ($value && \in_array($field['rgxp'], ['date', 'time', 'datim'], true)) {
            $objDate = new Date($value, Date::getFormatFromRgxp($field['rgxp']));
            $value = $objDate->tstamp;
        }

        return $value;
    }

    private function prepareLabel(mixed $value, array $field): mixed
    {
        $event = $this->eventDispatcher->dispatch(new FormDataLabelEvent($value, $field));

        if ($event->isPropagationStopped()) {
            return $event->getLabel();
        }

        if (\is_array($value)) {
            return array_map(fn ($v) => $this->prepareLabel($v, $field), $value);
        }

        if (Validator::isUuid($value) && null !== ($filesModel = FilesModel::findByUuid($value))) {
            return $filesModel->path;
        }

        if (!empty($field['options'])) {
            $options = StringUtil::deserialize($field['options'], true);

            foreach ($options as $option) {
                if ($option['value'] === $value && !empty($option['label'])) {
                    return $option['label'];
                }
            }
        }

        return '';
    }
}
