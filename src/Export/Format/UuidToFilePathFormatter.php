<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export\Format;

use Contao\FilesModel;
use Contao\Validator;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsFormatter;

#[AsLeadsFormatter('uuidToFilePath')]
class UuidToFilePathFormatter implements FormatterInterface
{
    public function format(mixed $value, string $type): int|string
    {
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
