<?php

namespace Pianissimo\Component\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class ClassNotFoundException extends Exception implements NotFoundExceptionInterface
{

}