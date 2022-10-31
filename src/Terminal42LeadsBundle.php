<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42LeadsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
