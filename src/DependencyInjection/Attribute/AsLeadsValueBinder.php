<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\DependencyInjection\Attribute;

/**
 * An attribute to tag a service as terminal42_leads.value_binder.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsLeadsValueBinder
{
    public function __construct(
        public string $type,
        public int $priority = 0,
    ) {
    }
}
