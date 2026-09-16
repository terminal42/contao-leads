<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Migration;

use Contao\CoreBundle\Migration\Version507\AbstractFieldPermissionMigration;

class FieldPermissionMigration extends AbstractFieldPermissionMigration
{
    protected function getMapping(): array
    {
        return [
            'leadp' => [
                'edit' => 'tl_lead_data::update',
                'delete' => 'tl_lead::delete',
            ],
        ];
    }
}
