<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FormDataValueEvent extends Event
{
    public function __construct(private mixed $value, private readonly array $field)
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

    public function setValue(mixed $value): self
    {
        $this->value = $value;
        $this->stopPropagation();

        return $this;
    }
}
