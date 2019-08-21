# The Dependency Injection Component
The Dependency Injection Component allows you to implement the dependency injection design pattern.

## Installation
```
composer require pianissimo-php/dependency-injection:master-dev
```

## What is a service?
A service is a object which can be injected as an dependency of your class.
The services are stored in the container.

## Defining services

The most easy way to create a container is to use the `ContainerBuilder`. The `ContainerBuilder` implements the `ContainerInterface`.
````PHP
$containerBuilder = new ContainerBuilder();
````

The `register()` method allows you to define a new service. It returns a `Definition` object.
You can describe `MailerService`'s constructor by adding arguments using `addArgument()`
````PHP
$containerBuilder
    ->register('mailer_service', MailerService::class)
    ->addArgument(new Reference('entity_manager_interface'))
    ->addArgument('SMTP');
````

You can also enable autowiring for the service definition:
````PHP
$containerBuilder
    ->register('entity_manager', EntityManager::class)
    ->setAutowired(true);
````

You can also autowire services using the `autowire` method:
````PHP
$containerBuilder->autowire(EntityManager::class);
````

The `Builder` class will autowire the `Definition` object.

You can also choose to autowire the services by default:
````PHP
$containerBuilder->setDefaultAutowiring(true);
````

You can also define the service to be used for the injection of an interface like this:
````PHP
$containerBuilder->add(EntityManagerInterface::class, new Reference('entity_manager'));
````

Add tags to your service definition using the `addTag()` method:
````PHP
$containerBuilder
    ->register('dashboard_controller', DashboardController::class)
    ->addTag('controller')
    ->setAutowired(true);
````

After the container is built, you can use the `findServicesByTag()` method to retrieve the tagged service definitions 
(e.g. to use in a compiler pass).

````PHP
$containerBuilder->findServicesByTag('controller');
````

## Building the container
Build the container using the `build` method:
````PHP
$containerBuilder->build();
````

The Builder class builds all definitions and autowires all definitions of which the autowiring is enabled.

## Compiler passes
You can add compiler passes to the container, these are processed after all definitions have been built.
Compiler passes must implement the `CompilerPassInterface`.
````PHP
$containerBuilder->addCompilerPass($compilerPass);
````

## Get a service from the container
When the container is built, you can get a service from the container after it like this:
````PHP
$mailerService = $containerBuilder->get('mailer_service');
````
The container loads the service when it is initialized, otherwise it will initialize the service using its built definition.

Avoid using the `get()` method, you should fetch your dependencies using the constructor.
If you use the MVC pattern, you should define your controllers as services 
and use the `get()` method in your controller resolver to initialize the controller.

## Injecting the container
The Dependency Injection Component does not allow you to inject the service container.
Your services should not be container aware, they do not need to know how and that they have been injected.
[Read more](https://stackoverflow.com/questions/10356497/is-is-an-anti-pattern-to-inject-di-container-to-almost-each-class)
