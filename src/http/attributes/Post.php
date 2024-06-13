<?php

namespace ngphp\http\attributes;

#[\Attribute]
class Post
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}