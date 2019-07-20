<?php

namespace Pianissimo\Component\Framework\Command;

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\DependencyInjection\ContainerInterface;
use Pianissimo\Component\Routing\RoutingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugRoutesCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(Container $container, string $name = null)
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
        /** @var RoutingService $routingService */
        $routingService = $this->container->autowire(RoutingService::class);
        $routingService->initializeRoutes();

        $routes = $routingService->getRegistry();

        $routeRows = [];

        foreach ($routes as $route) {
            $routeRows[] = [
                $route->getName(),
                $route->getPath(),
                $route->getClass(),
                $route->getFunction(),
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
