<?php

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

    private function loadServices()
    {
        if (null !== $this->instances) {
            return;
        }

        $this->instances = [];

        foreach ($this->services as $service) {
            if (!$service instanceof DataTransformerInterface) {
                throw new \RuntimeException(sprintf('"%s" must implement %s', get_class($service), DataTransformerInterface::class));
            }

            $this->instances[$service->getType()] = $service;
        }
    }
}
