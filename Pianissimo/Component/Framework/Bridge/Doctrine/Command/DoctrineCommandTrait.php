<?php

namespace Pianissimo\Component\Framework\Bridge\Doctrine\Command;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\Framework\Bridge\Doctrine\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait DoctrineCommandTrait
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $entityManager = $this->containerBuilder->get(EntityManager::class);
        $helperSet = ConsoleRunner::createHelperSet($entityManager);

        $this->setHelperSet($helperSet);

        return parent::execute($input, $output);
    }
}
