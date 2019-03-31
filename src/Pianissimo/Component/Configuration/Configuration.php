<?php

namespace App\Pianissimo\Component\Configuration;

class Configuration
{
    /** @var string */
    private $environment;

    /** @var array */
    private $controllers;

    /** @var array */
    private $annotations;

    public function __construct(array $data)
    {
        foreach ($data as $setting => $value) {
            $this->$setting = $value;
        }
    }

    public function get(string $setting)
    {
        return $this->$setting;
    }
}