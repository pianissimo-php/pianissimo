<?php

namespace App;

use Pianissimo\Component\Config\LoaderInterface;

class Core extends \Pianissimo\Component\Framework\Core
{
    public function configureContainer(LoaderInterface $loader): void
    {
        $configDir = $this->getProjectDir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        $loader->load($configDir . 'services.yaml');
    }
}
