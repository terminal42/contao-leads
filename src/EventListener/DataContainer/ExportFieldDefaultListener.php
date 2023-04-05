<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * Adds all fields as default export configuration.
 */
#[AsCallback('tl_lead_export', 'fields.fields.load')]
class ExportFieldDefaultListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(array|string|null $value, DataContainer $dc): array|string|null
    {
        if (!empty($value)) {
            return $value;
        }

        $config = StringUtil::deserialize($value, true);

        if (!empty($config) || !$dc->id) {
            return $value;
        }

        $config = [['field' => '_id'], ['field' => '_form'], ['field' => '_created'], ['field' => '_member']];

        $fieldIds = $this->connection->fetchFirstColumn(
            "SELECT id FROM tl_form_field WHERE leadStore!='' AND pid=(SELECT pid FROM tl_lead_export WHERE id=?) ORDER BY sorting",
            [(int) $dc->id]
        );

        foreach ($fieldIds as $id) {
            $config[] = [
                'field' => $id,
                'name' => '',
                'value' => 'all',
                'format' => '',
            ];
        }

        return serialize($config);
    }
}
