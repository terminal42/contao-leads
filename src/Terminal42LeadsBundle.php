<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Terminal42\LeadsBundle\DataTransformer\DataTransformerInterface;
use Terminal42\LeadsBundle\Exporter\ExporterInterface;

class Terminal42LeadsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(ExporterInterface::class)
            ->addTag('terminal42_leads.export')
        ;

        $container->registerForAutoconfiguration(DataTransformerInterface::class)
            ->addTag('terminal42_leads.data_transformer')
        ;
    }
}
