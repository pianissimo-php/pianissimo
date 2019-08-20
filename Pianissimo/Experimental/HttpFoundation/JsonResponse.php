<?php

namespace Pianissimo\Experimental\HttpFoundation;

class JsonResponse extends Response
{
    public function __construct($content)
    {
        $content = json_encode($content);
        parent::__construct($content);
    }
}
