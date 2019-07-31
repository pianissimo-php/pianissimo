## The Framework Component
The Framework Component 'glues' all components together into a framework.

##### WARNING: this component is in development and unstable to use.

### Core
It is common to use a Core class located in `src/Core.php` which extends `Pianissimo\Component\Framework\Core`.
This allows you to configure the Core to the requirements of the project.

#### Container
The Container is booted in the `handle()` method.
When the container is booted, the container is initialized and built.

The container is an instance of the `ContainerBuilder` class.
A container loader, an instance of a `Pianissimo\Component\Config\DelegatingLoader` is used to load the service definitions.
The `services.yaml` file of the Framework component is loaded by the `DelegatingLoader`.
````PHP
public function configureContainer(LoaderInterface $loader): void
{
    $configDir = $this->getProjectDir() . DIRECTORY_SEPARATOR . 'config';

    $loader->load($configDir . DIRECTORY_SEPARATOR . 'services.yaml');
}
````
If the `configureContainer()` method exists in the projects' Core class, it is called with the `DelegatingLoader` as an argument. 
In this way one or more configuration files with service definitions can be loaded via the `configureContainer()` method by adding them to the `DelegatingLoader`.

##### Service definitions
You can define your services in `services.yaml` like this:
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
