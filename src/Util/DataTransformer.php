<?php

namespace Terminal42\LeadsBundle\Util;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\LeadsBundle\DataTransformer\DataTransformerFactory;
use Terminal42\LeadsBundle\Event\TransformRowEvent;

class DataTransformer
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var DataTransformerFactory
     */
    private $transformerFactory;

    public function __construct(EventDispatcherInterface $eventDispatcher, DataTransformerFactory $transformerFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->transformerFactory = $transformerFactory;
    }

    /**
     * Returns the prepared row according to the Row configuration.
     *
     * @param array     $data
     * @param \stdClass $config
     * @param array     $columnConfigs
     *
     * @return array
     */
    public function compileRow(array $data, \stdClass $config, array $columnConfigs)
    {
        $compiled = array();

        foreach ($columnConfigs as $columnConfig) {
            $event = $this->eventDispatcher->dispatch(
                'terminal42_leads.transform_row',
                new TransformRowEvent($data, $config, $columnConfig)
            );

            if (null !== ($value = $event->getValue())) {
                $compiled[] = $value;
                continue;
            }

            // Regular form field
            if (isset($columnConfig['id'])) {
                $value = $data[$columnConfig['id']]['value'];
                $label = $columnConfig['label'];
            } else {
                // Internal field
                $row   = current($data);
                $value = $row[$columnConfig['valueColRef']];
                $label = $row[$columnConfig['labelColRef']];
            }

            $value      = $this->transformValue($value, $columnConfig);
            $label      = $this->prepareLabel($label);
            $compiled[] = $this->getValueForOutput($columnConfig['value'], $value, $label);

        }

        return $compiled;
    }

    /**
     * Transform the value to a desired format.
     *
     * @param               $value
     * @param array         $columnConfig
     *
     * @return string
     */
    public function transformValue($value, array $columnConfig)
    {
        $value = implode(', ', deserialize($value, true));

        // Merge transformers chosen by user (format) with an array of arbitrary ones
        // defined by any developer.
        if ($columnConfig['format']) {
            $columnConfig['transformers'] = array_merge(
                (array) $columnConfig['format'],
                (array) $columnConfig['transformers']
            );
        }

        $transformers = (array) $columnConfig['transformers'];

        foreach ($transformers as $transformerKey) {
            $value = $this->transformerFactory->createForType($transformerKey)->transform($value);
        }

        return (string) $value;
    }

    /**
     * Formats the value according a given output format.
     *
     * @param string    $outputFormat
     * @param  mixed    $value
     * @param null      $label
     *
     * @return string
     */
    public function getValueForOutput($outputFormat, $value, $label = null)
    {
        if ($outputFormat === 'value') {
            return $value;
        }

        if ($outputFormat === 'label') {
            return $label ?: $value;
        }

        if ($label === '' && $value === '') {
            return ''; // No label, no value
        }

        if ($label === '' && $value !== '') {
            return $value; // No label, but value
        }

        if ($label !== '' && $value === '') {
            return $label; // Label, no value
        }

        if ($label == $value) {
            return $value; // Label the same as value
        }

        return $label . ' [' . $value . ']'; // Different label and value
    }

    /**
     * Prepares the label. Can handle both, a regular and a serialized string.
     *
     * @param  string $label
     * @return string
     */
    private function prepareLabel($label)
    {
        if ($label != '') {
            $labelChunks = deserialize($label);

            if (is_array($labelChunks) && !empty($labelChunks)) {
                $label = implode(', ', $label);
                // TODO the second argument to implode() should probably be $labelChunks?
            }
        }

        return $label;
    }
}
