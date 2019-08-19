<?php

namespace Pianissimo\Component\Framework\Command;

use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\Framework\Router;
use Pianissimo\Component\Framework\Routing\AnnotatedRouteLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugRoutesCommand extends Command
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function __construct(ContainerBuilder $container, string $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure(): void
    {
        $this
            ->setName('debug:routes')
            ->setDescription('Greet someone')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        dd($this->container->findServicesByTag('controller'));

        /** @var Router $router */
        $router = $this->container->get(Router::class);

        /** @var AnnotatedRouteLoader $annotatedRouteLoader */
        $annotatedRouteLoader = $this->container->get(AnnotatedRouteLoader::class);


        $annotatedRouteLoader->load();

        $router->initializeRoutes();

        $routes = $routingService->getRoutes();

        $routeRows = [];

        foreach ($routes as $route) {
            $routeRows[] = [
                $route->getName(),
                $route->getPath(),
                $route->getClass(),
                $route->getMethod(),
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Route name', 'Path', 'Class', 'Function'])
            ->setRows($routeRows)
        ;
        $table->render();
    }
}
