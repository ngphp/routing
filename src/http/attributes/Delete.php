<?php

namespace ngphp\http\attributes;

#[\Attribute]
class Delete
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
