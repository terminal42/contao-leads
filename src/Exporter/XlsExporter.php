<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Exporter;

use Contao\File;
use Database\Result;

class XlsExporter extends AbstractExcelExporter
{
    /**
     * Exports a given set of data row ids using a given configuration.
     *
     * @param Result     $config
     * @param array|null $ids
     */
    public function export(\stdClass $config, $ids = null): File
    {
        return $this->exportWithFormat($config, $ids, 'Xls');
    }
}
