<?php

namespace Pianissimo\Component\Framework\Loader;

use Pianissimo\Component\Config\LoaderInterface;
use Pianissimo\Component\DependencyInjection2\ContainerBuilder;

abstract class FileLoader implements LoaderInterface
{
    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;
    }
}
