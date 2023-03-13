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

use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Contao\System;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractExcelExporter extends AbstractExporter
{
    /**
     * Returns true if available.
     */
    public function isAvailable(): bool
    {
        return class_exists(Spreadsheet::class);
    }

    /**
     * Exports based on Excel format.
     *
     * @throws \Exception
     */
    protected function exportWithFormat(\stdClass $config, ?array $ids, string $format): File
    {
        if ($config->useTemplate) {
            $sheet = $this->getSheetTemplate($config, $format);
        } else {
            $sheet = new Spreadsheet();
        }

        $sheet = $this->writeDataToSheet($sheet, $config, $ids);
        $writer = IOFactory::createWriter($sheet, $format);

        return $this->exportWriter($writer, $config);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception|\Exception
     */
    protected function exportWriter(IWriter $writer, \stdClass $config): File
    {
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');
        $fileName = 'system/tmp/'.$this->exportFile->getFilenameForConfig($config);
        $writer->save($projectDir.'/'.$fileName);

        $this->updateLastRun($config);

        return new File($fileName);
    }

    /**
     * @throws Exception|\Exception
     */
    protected function getSheetTemplate(\stdClass $config, string $format): Spreadsheet
    {
        // Fetch the template and make a copy of it
        $template = FilesModel::findByPk($config->template);

        if (null === $template) {
            $objResponse = new Response('Could not find template.', 500);
            $objResponse->send();
        }

        $tmpPath = 'system/tmp/'.$this->exportFile->getFilenameForConfig($config);
        Files::getInstance()->copy($template->path, $tmpPath);

        $reader = IOFactory::createReader($format);
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');
        $sheet = $reader->load($projectDir.'/'.$tmpPath);

        $sheet->setActiveSheetIndex((int)$config->sheetIndex);

        return $sheet;
    }

    /**
     * @throws Exception
     */
    protected function writeDataToSheet(Spreadsheet $sheet, \stdClass $config, $ids = null): Spreadsheet
    {
        $currentRow = (int)$config->startIndex ?: 1;
        $dataCollector = $this->prepareDefaultDataCollector($config, $ids);
        $columnConfigs = $this->prepareDefaultExportConfig($config, $dataCollector);

        // Add header fields
        if ($config->headerFields) {
            $headerRow = $this->prepareDefaultHeaderFields($config, $dataCollector);
            $currentRow = $this->writeRowToSheet($sheet, $currentRow, $headerRow, $config);
        }

        foreach ($dataCollector->getExportData() as $row) {
            $compiledRow = $this->dataTransformer->compileRow($row, $config, $columnConfigs);
            $currentRow = $this->writeRowToSheet($sheet, $currentRow, $compiledRow, $config);
        }

        $this->handleDefaultExportResult($currentRow);

        return $sheet;
    }

    /**
     * @throws Exception
     */
    protected function writeRowToSheet(Spreadsheet $sheet, int $currentRow, array $row, \stdClass $config): int
    {
        $currentColumn = 0;
        foreach ($row as $k => $value) {
            // Support explicit target column
            if ('tokens' === $config->export && isset($config->tokenFields[$k]['targetColumn'])) {
                $column = $config->tokenFields[$k]['targetColumn'];

                if (!is_numeric($column)) {
                    $column = Coordinate::columnIndexFromString($column) - 1;
                }
            } else {
                // Use next column, ignoring explicit target columns in the counter
                $column = ++$currentColumn;
            }
            $sheet->getActiveSheet()->getCell(
                Coordinate::stringFromColumnIndex($column).$currentRow
            )->setValueExplicit((string)$value, DataType::TYPE_STRING2);
        }
        ++$currentRow;

        return $currentRow;
    }
}
