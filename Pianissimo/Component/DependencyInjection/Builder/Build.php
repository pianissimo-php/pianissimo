<?php

namespace Pianissimo\Component\DependencyInjection\Builder;

use Pianissimo\Component\DependencyInjection\Definition;

class Build
{
    /**
     * @var object[]
     */
    private $services;

    /**
     * @var object[]
     */
    private $definitions;

    /**
     * @var string[]
     */
    private $serviceIds;

    public function __construct(array $services, array $definitions, array $serviceIds)
    {
        $this->services = $services;
        $this->definitions = $definitions;
        $this->serviceIds = $serviceIds;
    }

    /**
     * @return object[]
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @return Definition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @return string[]
     */
    public function getServiceIds(): array
    {
        return $this->serviceIds;
    }
}
