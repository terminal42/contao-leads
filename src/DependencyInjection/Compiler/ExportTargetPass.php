<?php

namespace Terminal42\LeadsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExportTargetPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('terminal42_leads.export_target_manager')) {
            return;
        }

        $definition     = $container->findDefinition('terminal42_leads.export_target_manager');
        $taggedServices = $container->findTaggedServiceIds('terminal42_leads.export_target');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addTarget', [new Reference($id), $attributes['alias']]);
            }
        }
    }
}
