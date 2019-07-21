<?php

namespace Pianissimo\Component\Framework\Loader;

use Pianissimo\Component\Config\LoaderInterface;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;

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
