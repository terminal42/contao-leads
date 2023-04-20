<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class FormConfigMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_form'])) {
            return false;
        }

        $formColumns = $schemaManager->listTableColumns('tl_form');

        return \array_key_exists('leadmaster', $formColumns) && !\array_key_exists('leadmain', $formColumns);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(
            'ALTER TABLE tl_form CHANGE COLUMN `leadMaster` `leadMain` int(10) unsigned NOT NULL DEFAULT 0'
        );

        $this->connection->executeStatement(
            "UPDATE tl_form SET leadLabel=REPLACE(leadLabel, '##created##', '##_created##') WHERE leadLabel!=''"
        );

        return $this->createResult(true);
    }
}
