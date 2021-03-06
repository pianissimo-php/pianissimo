# The Framework Component
The Framework Component 'glues' all components together into a framework.

##### WARNING: this component is in development and unstable to use.

## Core
It is common to use a Core class located in `src/Core.php` which extends `Pianissimo\Component\Framework\Core`.
This allows you to configure the Core to the requirements of the project.

### Container
The container is booted in the `handle()` method.
When the container is booted, the container is initialized and built.

The container is an instance of the `ContainerBuilder` class.
A container loader, an instance of a `Pianissimo\Component\Config\DelegatingLoader` is used to load the service definitions.
The `services.yaml` file of the Framework component is loaded by the `DelegatingLoader`.

If the `configureContainer()` method exists in the projects' Core class, it is called with the `DelegatingLoader` as an argument:
````PHP
public function configureContainer(DelegatingLoader $loader): void
{
    $configDir = $this->getProjectDir() . DIRECTORY_SEPARATOR . 'config';

    $loader->load($configDir . DIRECTORY_SEPARATOR . 'services.yaml');
}
````
In this way one or more configuration files with service definitions can be loaded via the `configureContainer()` method by adding them to the container loader.

### Service definitions
The Framework Component provides a `YamlFileLoader` which allows you to use YAML to define your service definitions.
The file for the service definitions is located by default in `config/services.yaml`.

You can define a service like this:
````YAML
services:
  entity_manager:
    class: App\Manager\EntityManager
    autowire: true
````
or you can define your services without autowiring them:
````YAML
services:
  mailer_service:
    class: App\Service\MailerService
    arguments: ['@entity_manager', 'SMTP']
````
As you see in the example above you can use an `@` character to refer to a service definition.

Use the `resource` key to define multiple services according to the `glob` pattern.
````YAML
services:
  App\Controller:
    resource: '../src/Controller/*'
    tags: ['controller']
````

### Controller resolver
After the container has been booted in the `handle()` method, the `ControllerResolver` is called.
The `AnnotatedRouteLoader` that implements the `Pianissimo\Component\Routing\RouteLoaderInterface` is used to load the - 
as the name suggests - annotated routes, defined in the controllers.
The `RouterControllerCompilerPass` is used to add the controller classes to the `AnnotatedRouteLoader`.

Class `Pianissimo\Component\Framework\Router` that implements the `Pianissimo\Component\Routing\RouterInterface` 
is used to match the route to a controller method.
