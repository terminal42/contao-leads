<?php

namespace Terminal42\LeadsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Terminal42\LeadsBundle\DependencyInjection\Compiler\ExportTargetPass;

class Terminal42LeadsBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExportTargetPass());
    }
}
