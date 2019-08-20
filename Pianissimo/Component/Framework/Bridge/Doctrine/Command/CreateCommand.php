<?php

namespace Pianissimo\Component\Framework\Bridge\Doctrine\Command;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand as DoctrineCreateCommand;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;

class CreateCommand extends DoctrineCreateCommand
{
    use DoctrineCommandTrait;

    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;

        parent::__construct();

        $this->setName('doctrine:schema:create');
    }
}
