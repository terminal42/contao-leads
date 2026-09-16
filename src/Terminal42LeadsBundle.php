<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle;

use Dompdf\Dompdf;
use Mpdf\Mpdf;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsFormatter;
use Terminal42\LeadsBundle\Export\PhpSpreadsheetExporter;

class Terminal42LeadsBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $loader = new YamlFileLoader($builder, new FileLocator(__DIR__.'/../config'));
        $loader->load('services.yaml');

        $builder->registerAttributeForAutoconfiguration(
            AsLeadsExporter::class,
            static function (ChildDefinition $definition, AsLeadsExporter $attribute): void {
                $definition->addTag('terminal42_leads.exporter', get_object_vars($attribute));
            },
        );

        $builder->registerAttributeForAutoconfiguration(
            AsLeadsFormatter::class,
            static function (ChildDefinition $definition, AsLeadsFormatter $attribute): void {
                $definition->addTag('terminal42_leads.formatter', get_object_vars($attribute));
            },
        );

        if (ContainerBuilder::willBeAvailable('tecnickcom/tcpdf', \TCPDF::class, [])) {
            $builder->getDefinition(PhpSpreadsheetExporter::class)->addTag('terminal42_leads.exporter', ['type' => 'tcpdf']);
        }

        if (ContainerBuilder::willBeAvailable('dompdf/dompdf', Dompdf::class, [])) {
            $builder->getDefinition(PhpSpreadsheetExporter::class)->addTag('terminal42_leads.exporter', ['type' => 'dompdf']);
        }

        if (ContainerBuilder::willBeAvailable('mpdf/mpdf', Mpdf::class, [])) {
            $builder->getDefinition(PhpSpreadsheetExporter::class)->addTag('terminal42_leads.exporter', ['type' => 'mpdf']);
        }
    }
}
