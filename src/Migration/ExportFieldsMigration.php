<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Terminal42\LeadsBundle\Export\ExporterInterface;

class ExportFieldsMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (
            !$schemaManager->tablesExist('tl_lead_export')
            || !\array_key_exists('fields', $schemaManager->listTableColumns('tl_lead_export'))
        ) {
            return false;
        }

        return $this->connection->fetchOne(
            "SELECT COUNT(*) FROM tl_lead_export WHERE export='fields' AND ((fields LIKE '%s:5:\"value\";%' AND fields NOT LIKE '%\"output\";s:5:\"value\";%') OR fields LIKE '%\"raw\"%')"
        ) > 0;
    }

    public function run(): MigrationResult
    {
        $configs = $this->connection->fetchAllKeyValue(
            "SELECT id, fields FROM tl_lead_export WHERE export='fields' AND ((fields LIKE '%s:5:\"value\";%' AND fields NOT LIKE '%\"output\";s:5:\"value\";%') OR fields LIKE '%\"raw\"%')"
        );

        foreach ($configs as $id => $fields) {
            $fields = StringUtil::deserialize($fields);

            if (!\is_array($fields)) {
                continue;
            }

            foreach ($fields as &$field) {
                if (isset($field['value'])) {
                    $field['output'] = $field['value'];
                    unset($field['value']);
                }

                if ('all' === ($field['output'] ?? null)) {
                    $field['output'] = ExporterInterface::OUTPUT_BOTH;
                }

                if ('raw' === ($field['format'] ?? null)) {
                    $field['format'] = '';
                }
            }

            unset($field);

            $this->connection->update('tl_lead_export', ['fields' => serialize($fields)], ['id' => $id]);
        }

        return $this->createResult(true);
    }
}
