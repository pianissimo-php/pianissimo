<?php

namespace Pianissimo\Component\Framework\Bridge\Doctrine;

use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntityManager extends DoctrineEntityManager
{
    /**
     * @throws ORMException
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $isDevEnvironment = $parameterBag->get('environment') === 'dev';
        $srcDirectory = $parameterBag->get('project_dir') . DIRECTORY_SEPARATOR . 'src';

        $config = Setup::createAnnotationMetadataConfiguration([$srcDirectory], $isDevEnvironment, null, null, false);

        $connection = [
            'driver' => 'pdo_sqlite',
            'path' => $parameterBag->get('project_dir') . '/var/pianissimo.db',
        ];

        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        $connection = static::createConnection($connection, $config);

        parent::__construct($connection, $config, $connection->getEventManager());
    }
}
