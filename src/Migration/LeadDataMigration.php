<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

class LeadDataMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $this->shouldMigrateField('tl_lead', 'master_id', 'main_id', $schemaManager)
            || $this->shouldMigrateField('tl_lead_data', 'master_id', 'main_id', $schemaManager);
    }

    /**
     * @noinspection SqlWithoutWhere
     */
    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        if ($this->shouldMigrateField('tl_lead', 'master_id', 'main_id', $schemaManager)) {
            $this->connection->executeStatement(
                'ALTER TABLE tl_lead CHANGE COLUMN `master_id` `main_id` int(10) UNSIGNED NOT NULL DEFAULT 0'
            );
        }

        if ($this->shouldMigrateField('tl_lead_data', 'master_id', 'main_id', $schemaManager)) {
            $this->connection->executeStatement(
                'ALTER TABLE tl_lead_data ADD COLUMN `main_id` int(10) UNSIGNED NOT NULL DEFAULT 0'
            );

            $this->connection->executeStatement('UPDATE tl_lead_data SET main_id = form_id');
            $this->connection->executeStatement('UPDATE tl_lead_data SET form_id = master_id');
            $this->connection->executeStatement('ALTER TABLE tl_lead_data DROP COLUMN `master_id`');
        }

        return $this->createResult(true);
    }

    private function shouldMigrateField(string $table, string $oldField, string $newField, AbstractSchemaManager $schemaManager): bool
    {
        if (!$schemaManager->tablesExist([$table])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns($table);

        return \array_key_exists($oldField, $columns) && !\array_key_exists($newField, $columns);
    }
}
