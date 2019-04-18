<?php

namespace Pianissimo\Component\Core;

use Pianissimo\Component\Container\Container;
use Pianissimo\Component\Core\Command\DebugRoutesCommand;

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
            DebugRoutesCommand::class,
        ];
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}