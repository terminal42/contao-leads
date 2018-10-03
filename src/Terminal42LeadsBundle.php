<?php

namespace Terminal42\LeadsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Terminal42\LeadsBundle\DataTransformer\DataTransformerInterface;
use Terminal42\LeadsBundle\Export\ExportInterface;

class Terminal42LeadsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerForAutoconfiguration(ExportInterface::class)
            ->addTag('terminal42_leads.export');

        $container->registerForAutoconfiguration(DataTransformerInterface::class)
            ->addTag('terminal42_leads.data_transformer');
    }
}
