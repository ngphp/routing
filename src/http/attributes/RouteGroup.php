<?php

namespace ngphp\http\attributes;

#[\Attribute]
class RouteGroup
{
    public string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }
}