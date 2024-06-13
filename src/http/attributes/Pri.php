<?php

namespace ngphp\http\attributes;

#[\Attribute]
class Pri
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
