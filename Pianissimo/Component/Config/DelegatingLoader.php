<?php

namespace Pianissimo\Component\Config;

class DelegatingLoader implements LoaderInterface
{
    /**
     * @var LoaderResolver
     */
    private $loaderResolver;

    public function __construct(LoaderResolver $loaderResolver)
    {
        $this->loaderResolver = $loaderResolver;
    }

    public function load(string $file)
    {
        $loader = $this->loaderResolver->resolve($file);

        return $loader->load($file);
    }

    public function supports(): array
    {
        return $this->loaderResolver->supports();
    }
}
