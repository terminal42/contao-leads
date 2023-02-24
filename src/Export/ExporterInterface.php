<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export;

use Symfony\Component\HttpFoundation\Response;

interface ExporterInterface
{
    public const EXPORT_ALL = 'all';
    public const EXPORT_FIELDS = 'fields';
    public const EXPORT_TOKENS = 'tokens';

    public const OUTPUT_LABEL = 'label';
    public const OUTPUT_VALUE = 'value';
    public const OUTPUT_BOTH = 'both';

    public const EOL = [
        'r' => "\r",
        'n' => "\n",
        'rn' => "\r\n",
    ];

    public function getResponse(array $config, array $ids = null): Response;

    public function writeToFile(array $config, string $filename, array $ids = null): void;
}
