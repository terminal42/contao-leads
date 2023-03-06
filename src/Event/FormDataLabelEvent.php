<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FormDataLabelEvent extends Event
{
    private mixed $label = '';

    public function __construct(private readonly mixed $value, private readonly array $field)
    {
    }

    public function getField(): array
    {
        return $this->field;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getLabel(): mixed
    {
        return $this->label;
    }

    public function setLabel(mixed $label): self
    {
        $this->label = $label;
        $this->stopPropagation();

        return $this;
    }
}
