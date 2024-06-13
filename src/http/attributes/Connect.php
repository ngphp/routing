<?php

namespace ngphp\http\attributes;

#[\Attribute]
class Connect
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}