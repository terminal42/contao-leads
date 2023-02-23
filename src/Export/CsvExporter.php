<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export;

use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;

#[AsLeadsExporter('csv')]
class CsvExporter extends AbstractExporter
{
    protected function doExport($stream): void
    {
        $separator = $this->getConfig()['csvSeparator'] ?? ',';
        $enclosure = $this->getConfig()['csvEnclosure'] ?? '"';
        $escape = $this->getConfig()['csvEscape'] ?? '\\';
        $eol = self::EOL[$this->getConfig()['eol']] ?? "\n";

        foreach ($this->iterateRows() as $data) {
            fputcsv($stream, $data, $separator, $enclosure, $escape, $eol);
        }
    }
}
