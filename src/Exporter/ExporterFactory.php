<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Exporter;

use Doctrine\DBAL\Connection;

class ExporterFactory
{
    /**
     * @var iterable
     */
    private $services;

    /**
     * @var ExporterInterface[]
     */
    private $instances;

    /**
     * @var Connection
     */
    private $database;

    public function __construct(iterable $services, Connection $database)
    {
        $this->database = $database;
        $this->services = $services;
    }

    /**
     * @return ExporterInterface[]
     */
    public function getServices(): array
    {
        $this->loadServices();

        return $this->instances;
    }

    public function createForType(string $type): ExporterInterface
    {
        $this->loadServices();

        if (!isset($this->instances[$type])) {
            throw new \InvalidArgumentException(sprintf('Export type "%s" does not exist.', $type));
        }

        return $this->instances[$type];
    }

    public function buildConfig(int $configId): \stdClass
    {
        $qb = $this->database->createQueryBuilder();

        $qb
            ->select('e.*')
            ->addSelect('f.leadMaster AS master')
            ->from('tl_lead_export', 'e')
            ->leftJoin('e', 'tl_form', 'f', 'f.id = e.pid')
            ->where('e.id = :id')
            ->setParameter('id', $configId)
        ;

        $result = $qb->execute();

        if (!$result->rowCount()) {
            throw new \InvalidArgumentException(sprintf('Export config ID %s not found', $configId));
        }

        $config = (object) $result->fetchAssociative();

        $config->master = $config->master ?: $config->pid;
        $config->fields = \Contao\StringUtil::deserialize($config->fields, true);
        $config->tokenFields = \Contao\StringUtil::deserialize($config->tokenFields, true);

        return $config;
    }

    private function loadServices(): void
    {
        if (null !== $this->instances) {
            return;
        }

        $this->instances = [];

        foreach ($this->services as $service) {
            if (!$service instanceof ExporterInterface) {
                throw new \RuntimeException(sprintf('"%s" must implement %s', \get_class($service), ExporterInterface::class));
            }

            if (!$service->isAvailable()) {
                return;
            }

            $this->instances[$service->getType()] = $service;
        }
    }
}
