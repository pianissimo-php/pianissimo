<?php

namespace Pianissimo\Component\Framework\Bridge\Doctrine;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntityManager extends \Doctrine\ORM\EntityManager
{
    /**
     * @throws ORMException
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $isDevMode = true;
        $srcDirectory = $parameterBag->get('project.dir') . DIRECTORY_SEPARATOR . 'src';

        $config = Setup::createAnnotationMetadataConfiguration([$srcDirectory], $isDevMode, null, null, false);

        $connection = [
            'driver' => 'pdo_sqlite',
            'path' => $parameterBag->get('project.dir') . '/var/db.sqlite',
        ];

        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        $connection = static::createConnection($connection, $config);

        parent::__construct($connection, $config, $connection->getEventManager());
    }
}
