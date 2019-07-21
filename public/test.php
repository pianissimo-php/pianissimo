<?php

include '../vendor/autoload.php';

use App\Manager\EntityManager;
use App\Manager\EntityManagerInterface;
use App\Service\MailerService;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\DependencyInjection\Reference;

function dump($var, $return = false)
{
    $dump = '<pre style="border: 2px solid black; padding: 20px;">' . print_r($var, true) . '</pre>';

    if ($return === true) {
        return $dump;
    }
    echo $dump;
}

function dd($var):void
{
    dump($var);
    die;
}

$containerBuilder = new ContainerBuilder();

$containerBuilder
    ->setParameter('parameter', 'foo.bar');

$containerBuilder
    ->register('entity.manager', EntityManager::class)
    ->setAutowired(true);

$containerBuilder
    ->register('mailer.service', MailerService::class)
    ->setAutowired(true);

$containerBuilder->add(EntityManagerInterface::class, new Reference('entity.manager'));

$containerBuilder->build();

$mailerService = $containerBuilder->get('mailer.service');
dd($mailerService);
