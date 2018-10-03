<?php

namespace Terminal42\LeadsBundle\Export;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class ExportFactory
{
    /**
     * @var Connection
     */
    private $database;

    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    public function createForType(string $type): ExportInterface
    {
        return new $GLOBALS['LEADS_EXPORT'][$type]();
    }

    public function buildConfig(int $configId): \stdClass
    {
        $qb = $this->database->createQueryBuilder();

        $qb
            ->select('e.*')
            ->addSelect('f.leadMaster AS master')
            ->from('tl_lead_export', 'e')
            ->leftJoin('e', 'tl_form', 'f', 'tl_form.id = e.pid')
            ->where('e.id = :id')
            ->setParameter('id', $configId)
        ;

        $result = $qb->execute();

        if (!$result->rowCount()) {
            throw new \InvalidArgumentException(sprintf('Export config ID %s not found', $configId));
        }

        $config = $result->fetch(FetchMode::STANDARD_OBJECT);

        $config->master      = $config->master ?: $config->pid;
        $config->fields      = deserialize($config->fields, true);
        $config->tokenFields = deserialize($config->tokenFields, true);

        return $config;
    }
}
