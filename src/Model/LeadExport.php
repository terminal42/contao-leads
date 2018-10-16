<?php

namespace Terminal42\LeadsBundle\Model;

use Contao\Model;

/**
 * @property int    $pid
 * @property string $name
 * @property string $type
 * @property string $filename
 * @property bool   $useTemplate
 * @property string $template
 * @property string $startIndex
 * @property string $sheetIndex
 * @property bool   $headerFields
 * @property string $export
 * @property bool   $cliExport
 * @property string $targetPath
 * @property array  $fields
 * @property array  $tokenFields
 * @property string $lastRun
 * @property bool   $skipLastRun
 */
class LeadExport extends Model
{
    protected static $strTable = 'tl_lead_export';
}
