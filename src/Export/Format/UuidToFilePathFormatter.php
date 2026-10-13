<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export\Format;

use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsFormatter;

#[AsLeadsFormatter('uuidToFilePath')]
class UuidToFilePathFormatter implements FormatterInterface
{
    public function format(mixed $value, string $type): int|string
    {
        if (\is_array($values = StringUtil::deserialize($value))) {
            return serialize(array_map(fn ($v) => $this->format($v, $type), $values));
        }

        if (!Validator::isUuid($value)) {
            return $value;
        }

        $filesModel = FilesModel::findByUUid($value);

        if (null === $filesModel) {
            return $value;
        }

        return $filesModel->path;
    }
}
