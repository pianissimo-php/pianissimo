<?php

namespace Pianissimo\Component\DependencyInjection2\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class ClassNotFoundException extends Exception implements NotFoundExceptionInterface
{

}
