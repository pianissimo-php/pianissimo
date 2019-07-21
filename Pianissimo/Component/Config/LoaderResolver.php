<?php

namespace Pianissimo\Component\Config;

use InvalidArgumentException;
use RuntimeException;

class LoaderResolver
{
    /**
     * @var LoaderInterface[]|array
     */
    private $loaders;

    /**
     * @var string[]|array
     */
    private $support = [];

    public function __construct(array $loaders)
    {
        foreach ($loaders as $loader) {
            if (!$loader instanceof LoaderInterface) {
                throw new InvalidArgumentException('Loader must implement LoaderInterface');
            }
            array_merge($this->supports(), $loader->supports());
        }

        $this->loaders = $loaders;
    }

    public function resolve(string $file): LoaderInterface
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        foreach ($this->loaders as $loader) {
            if (in_array($extension, $loader->supports(), true)) {
                return $loader;
            }
        }

        throw new RuntimeException(sprintf("No resolver found for type '%s'", $extension));
    }

    public function supports(): array
    {
        return $this->support;
    }
}
