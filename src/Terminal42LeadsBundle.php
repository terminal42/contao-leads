<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Terminal42\LeadsBundle\DataTransformer\DataTransformerInterface;
use Terminal42\LeadsBundle\Export\ExportInterface;

class Terminal42LeadsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(ExportInterface::class)
            ->addTag('terminal42_leads.export')
        ;

        $container->registerForAutoconfiguration(DataTransformerInterface::class)
            ->addTag('terminal42_leads.data_transformer')
        ;
    }
}
