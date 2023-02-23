<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\DependencyInjection\Attribute;

/**
 * An attribute to tag a service as terminal42_leads.exporter.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsLeadsExporter
{
    public function __construct(
        public string $type,
        public int $priority = 0,
    ) {
    }
}
