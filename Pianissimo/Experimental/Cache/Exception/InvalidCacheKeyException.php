<?php

namespace Pianissimo\Experimental\Cache\Exception;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidCacheKeyException extends Exception implements InvalidArgumentException
{

}
