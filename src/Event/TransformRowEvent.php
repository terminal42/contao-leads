<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TransformRowEvent extends Event
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var \stdClass
     */
    private $config;

    /**
     * @var array
     */
    private $columnConfig;

    /**
     * @var string|null
     */
    private $value;

    public function __construct(array $data, \stdClass $config, array $columnConfig)
    {
        $this->data = $data;
        $this->config = $config;
        $this->columnConfig = $columnConfig;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getConfig(): \stdClass
    {
        return $this->config;
    }

    public function getColumnConfig(): array
    {
        return $this->columnConfig;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        $this->stopPropagation();

        return $this;
    }
}
