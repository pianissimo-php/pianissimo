<?php

namespace App;

use Pianissimo\Component\Config\DelegatingLoader;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;

class Core extends \Pianissimo\Component\Framework\Core
{
    public function configureContainer(DelegatingLoader $loader): void
    {
        $configDir = $this->getProjectDir() . DIRECTORY_SEPARATOR . 'config';

        $loader->load($configDir . DIRECTORY_SEPARATOR . 'services.yaml');
    }

    public function buildContainer(ContainerBuilder $containerBuilder)
    {

    }
}
