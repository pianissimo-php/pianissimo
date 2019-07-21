<?php

namespace Pianissimo\Component\Config;

interface LoaderInterface
{
    public function load(string $file);

    public function supports(): array;
}
