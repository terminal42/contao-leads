<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener;

use Codefog\HasteBundle\StringParser;
use Contao\StringUtil;
use Terminal42\LeadsBundle\Event\TransformRowEvent;
use Terminal42\LeadsBundle\Util\DataTransformer;

class TokenRowListener
{
    private DataTransformer $dataTransformer;
    private StringParser $stringParser;

    public function __construct(DataTransformer $dataTransformer, StringParser $stringParser)
    {
        $this->dataTransformer = $dataTransformer;
        $this->stringParser = $stringParser;
    }

    public function onTransformRow(TransformRowEvent $event): void
    {
        $data = $event->getData();
        $config = $event->getConfig();
        $columnConfig = $event->getColumnConfig();

        if ('tokens' !== $config->export) {
            return;
        }

        $tokens = [];

        foreach ($columnConfig['allFieldsConfig'] as $fieldConfig) {
            $value = '';

            if (isset($data[$fieldConfig['id']])) {
                $value = $data[$fieldConfig['id']]['value'];
                $value = StringUtil::deserialize($value);

                // Add multiple tokens (<fieldname>_<option_name>) for multi-choice fields
                if (\is_array($value)) {
                    foreach ($value as $choice) {
                        $tokens[$fieldConfig['name'].'_'.$choice] = 1;
                    }
                }

                $value = $this->dataTransformer->transformValue($data[$fieldConfig['id']]['value'], $fieldConfig);
            }

            $tokens[$fieldConfig['name']] = $value;
        }

        $event->setValue($this->stringParser->recursiveReplaceTokensAndTags($columnConfig['tokensValue'], $tokens));
    }
}
