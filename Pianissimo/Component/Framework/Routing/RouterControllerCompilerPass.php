<?php

namespace Pianissimo\Component\Framework\Routing;

use Pianissimo\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;

class RouterControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $result = $containerBuilder->findServicesByTag('framework_annotated_route_loader');

        if (count($result) === 0) {
            return;
        }

        $annotatedRouteLoaderDefinition = $result[0];

        $definitions = $containerBuilder->findServicesByTag('controller');

        foreach ($definitions as $definition) {
            $annotatedRouteLoaderDefinition->addMethodCall('addController', [$definition->getClass()]);
        }
    }
}
