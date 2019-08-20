<?php

namespace Pianissimo\Component\Framework\Bridge\Doctrine\Command;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand as DoctrineUpdateCommand;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;

class UpdateCommand extends DoctrineUpdateCommand
{
    use DoctrineCommandTrait;

    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;

        parent::__construct();

        $this->setName('doctrine:schema:update');
    }
}
