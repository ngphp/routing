<?php

namespace ngphp\http\attributes;

#[\Attribute]
class Head
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
