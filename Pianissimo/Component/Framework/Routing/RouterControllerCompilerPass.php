<?php

namespace Pianissimo\Component\Framework\Routing;

use Pianissimo\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;

class RouterControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $annotatedRouteLoaderDefinitions = $containerBuilder->findServicesByTag('framework_annotated_route_loader');

        if (count($annotatedRouteLoaderDefinitions) === 0) {
            return;
        }

        foreach ($annotatedRouteLoaderDefinitions as $annotatedRouteLoaderDefinition) {
            $definitions = $containerBuilder->findServicesByTag('controller');

            foreach ($definitions as $key => $definition) {
                $annotatedRouteLoaderDefinition->addMethodCall('addController', [$key]);
            }
        }
    }
}
