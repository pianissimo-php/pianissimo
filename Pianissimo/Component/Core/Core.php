<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\Core\Command\GreetCommand;

class Core
{
    /** @var Container */
    protected $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function getCommands(): array
    {
        return [
            GreetCommand::class,
        ];
    }
}