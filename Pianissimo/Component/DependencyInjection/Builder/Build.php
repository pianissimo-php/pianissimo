<?php

namespace Pianissimo\Component\DependencyInjection\Builder;

use Pianissimo\Component\DependencyInjection\Definition;

class Build
{
    /**
     * @var object[]|array
     */
    private $definitions;

    /**
     * @var string[]|array
     */
    private $serviceIds;

    public function __construct(array $definitions, array $serviceIds)
    {
        $this->definitions = $definitions;
        $this->serviceIds = $serviceIds;
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
