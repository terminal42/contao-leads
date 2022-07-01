<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Exporter;

use Contao\File;

interface ExporterInterface
{
    public function getType(): string;

    public function getLabel(): string;

    public function isAvailable(): bool;

    public function export(\stdClass $config, $ids = null): File;
}
