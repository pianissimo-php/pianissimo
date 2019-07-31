<?php

namespace Pianissimo\Component\DependencyInjection\Compiler;

use Pianissimo\Component\DependencyInjection\ContainerBuilder;

interface CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder);
}
