<?php

namespace Pianissimo\Component\DependencyInjection\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class ClassNotFoundException extends Exception implements NotFoundExceptionInterface
{

}
