<?php

namespace ngphp\http\attributes;

#[\Attribute]
class Patch
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}