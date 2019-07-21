<?php

namespace Pianissimo\Component\DependencyInjection\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface
{

}
