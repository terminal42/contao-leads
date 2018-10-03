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

namespace Terminal42\LeadsBundle\DataTransformer;

class DataTransformerFactory
{
    /**
     * @var iterable
     */
    private $services;

    /**
     * @var DataTransformerInterface[]
     */
    private $instances;

    public function __construct(iterable $services)
    {
        $this->services = $services;
    }

    /**
     * @return DataTransformerInterface[]
     */
    public function getServices(): array
    {
        $this->loadServices();

        return $this->instances;
    }

    public function createForType(string $type): DataTransformerInterface
    {
        $this->loadServices();

        if (!isset($this->instances[$type])) {
            throw new \InvalidArgumentException(sprintf('Unknown data transformer type "%s"', $type));
        }

        return $this->instances[$type];
    }

    private function loadServices(): void
    {
        if (null !== $this->instances) {
            return;
        }

        $this->instances = [];

        foreach ($this->services as $service) {
            if (!$service instanceof DataTransformerInterface) {
                throw new \RuntimeException(sprintf('"%s" must implement %s', \get_class($service), DataTransformerInterface::class));
            }

            $this->instances[$service->getType()] = $service;
        }
    }
}
