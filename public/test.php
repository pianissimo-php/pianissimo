<?php

include '../vendor/autoload.php';

use App\Manager\EntityManager;
use App\Service\MailerService;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\DependencyInjection\Reference;

$containerBuilder = new ContainerBuilder();

$containerBuilder
    ->setParameter('parameter', 'foo.bar');

$containerBuilder
    ->register('entity.manager', EntityManager::class)
    ->setAutowired(true);

$containerBuilder
    ->register('mailer.service', MailerService::class)
    ->addArgument(new Reference('entity.manager'))
    ->addArgument('SMTP');
