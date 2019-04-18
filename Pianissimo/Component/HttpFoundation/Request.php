<?php

namespace Pianissimo\Component\HttpFoundation;

class Request
{
    /** @var array */
    private $queryParameters;

    public function __construct()
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $queryParameters);
            $this->queryParameters = $queryParameters;
        }
    }

    public function get(string $parameterName, $defaultValue)
    {
        if (isset($this->queryParameters[$parameterName]) === true) {
            return $this->queryParameters[$parameterName];
        }

        return $defaultValue;
    }
}