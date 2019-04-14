<?php

namespace Pianissimo\Component\Core\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{

}