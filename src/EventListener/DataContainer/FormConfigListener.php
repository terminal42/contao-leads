<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_form', 'config.onload')]
class FormConfigListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(DataContainer $dc): void
    {
        if ($dc->id && $this->connection->fetchOne('SELECT leadMain FROM tl_form WHERE id=?', [$dc->id])) {
            return;
        }

        PaletteManipulator::create()
            ->addField('leadMenuLabel', null, PaletteManipulator::POSITION_APPEND)
            ->addField('leadLabel', null, PaletteManipulator::POSITION_APPEND)
            ->addField('leadPeriod', null, PaletteManipulator::POSITION_APPEND)
            ->addField('leadPurgeUploads', null, PaletteManipulator::POSITION_APPEND)
            ->applyToSubpalette('leadEnabled', 'tl_form')
        ;
    }
}
