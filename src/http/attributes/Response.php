<?php

namespace ngphp\http\attributes;

class Response
{
    protected $statusCode;
    protected $headers = [];
    protected $body;

    public function __construct($body = '', $statusCode = 200, $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function send()
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }
        echo $this->body;
    }

    public function withStatus($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function withHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function withBody($body)
    {
        $this->body = $body;
        return $this;
    }
}
