<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\DependencyInjection;

use Dompdf\Dompdf;
use Mpdf\Mpdf;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsFormatter;
use Terminal42\LeadsBundle\Export\PhpSpreadsheetExporter;

class Terminal42LeadsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $container->registerAttributeForAutoconfiguration(
            AsLeadsExporter::class,
            static function (ChildDefinition $definition, AsLeadsExporter $attribute): void {
                $definition->addTag('terminal42_leads.exporter', get_object_vars($attribute));
            },
        );

        $container->registerAttributeForAutoconfiguration(
            AsLeadsFormatter::class,
            static function (ChildDefinition $definition, AsLeadsFormatter $attribute): void {
                $definition->addTag('terminal42_leads.formatter', get_object_vars($attribute));
            },
        );

        if (ContainerBuilder::willBeAvailable('tecnickcom/tcpdf', \TCPDF::class, [])) {
            $container->getDefinition(PhpSpreadsheetExporter::class)->addTag('terminal42_leads.exporter', ['type' => 'tcpdf']);
        }

        if (ContainerBuilder::willBeAvailable('dompdf/dompdf', Dompdf::class, [])) {
            $container->getDefinition(PhpSpreadsheetExporter::class)->addTag('terminal42_leads.exporter', ['type' => 'dompdf']);
        }

        if (ContainerBuilder::willBeAvailable('mpdf/mpdf', Mpdf::class, [])) {
            $container->getDefinition(PhpSpreadsheetExporter::class)->addTag('terminal42_leads.exporter', ['type' => 'mpdf']);
        }
    }
}
