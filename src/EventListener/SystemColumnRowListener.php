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

namespace Terminal42\LeadsBundle\EventListener;

use Terminal42\LeadsBundle\Event\TransformRowEvent;
use Terminal42\LeadsBundle\Leads;
use Terminal42\LeadsBundle\Util\DataTransformer;

class SystemColumnRowListener
{
    /**
     * @var DataTransformer
     */
    private $dataTransformer;

    public function __construct(DataTransformer $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;
    }

    public function onTransformRow(TransformRowEvent $event): void
    {
        $systemColumns = Leads::getSystemColumns();
        $columnConfig = $event->getColumnConfig();

        if (!isset($columnConfig['field'])
            || '_field' === $columnConfig['field']
            || !array_key_exists($columnConfig['field'], $systemColumns)
        ) {
            return;
        }

        $firstEntry = reset($data);
        $systemColumnConfig = $systemColumns[$columnConfig['field']];

        $value = (isset($systemColumnConfig['valueColRef']) ? $firstEntry[$systemColumnConfig['valueColRef']] : null);
        $value = $this->dataTransformer->transformValue($value, $systemColumnConfig);

        $value = $this->dataTransformer->getValueForOutput(
            $systemColumnConfig['value'],
            $value,
            (isset($systemColumnConfig['labelColRef']) ? $firstEntry[$systemColumnConfig['labelColRef']] : null)
        );

        $event->setValue($value);
    }
}
