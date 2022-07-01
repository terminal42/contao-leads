<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener;

use Terminal42\LeadsBundle\Event\TransformRowEvent;
use Terminal42\LeadsBundle\Model\Lead;
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
        $systemColumns = Lead::getSystemColumns();
        $columnConfig = $event->getColumnConfig();

        if (
            !isset($columnConfig['field'])
            || '_field' === $columnConfig['field']
            || !\array_key_exists($columnConfig['field'], $systemColumns)
        ) {
            return;
        }

        $firstEntry = array_values($event->getData())[0];
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
