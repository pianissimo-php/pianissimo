<?php

include '../vendor/autoload.php';

use App\Controller\IndexController;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\DependencyInjection\Reference;
use Pianissimo\Component\Framework\Bridge\Doctrine\EntityManager;
use Pianissimo\Component\Framework\Router;
use Pianissimo\Component\Routing\RouterInterface;

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

$containerBuilder
    ->autowire(IndexController::class);

$containerBuilder
    ->autowire(Router::class);

$containerBuilder
    ->add(EntityManagerInterface::class, new Reference('entity.manager'))
    ->add(RouterInterface::class, new Reference(Router::class));

$containerBuilder
    ->setDefaultAutowiring(true);

$containerBuilder
    ->setParameter('environment', 'dev')
    ->setParameter('cache_dir', '../var/cache')
    ->setParameter('templates_dir', '../templates')
    ->setParameter('project_dir', __DIR__ . '/../');

$containerBuilder->build();

$indexController = $containerBuilder->get(IndexController::class);
dump($indexController);

$mailerService = $containerBuilder->get('mailer.service');
dump($mailerService);
