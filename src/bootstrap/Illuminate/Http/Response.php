<?php

namespace Illuminate;

class Request
{
    protected $query = [];
    protected $post = [];
    protected $server = [];
    protected $headers = [];

    public static function capture()
    {
        $request = new self();
        $request->query = $_GET;
        $request->post = $_POST;
        $request->server = $_SERVER;
        $request->headers = self::parseHeaders();
        return $request;
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function header(string $key)
    {
        return $this->headers[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->query[$key]);
    }

    public function get(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function merge(array $input): void
    {
        foreach ($input as $key => $value) {
            $this->post[$key] = $value;
        }
    }

    private static function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = str_replace('HTTP_', '', $key);
                $headerKey = str_replace('_', '-', strtolower($headerKey));
                $headers[ucwords($headerKey, '-')] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        return $headers;
    }
}

class Response
{
    public function __construct(
        protected $content = '',
        protected $status = 200,
        protected $headers = []
    ) {}

    public static function json($data, $status = 200)
    {
        return new self(
            json_encode($data, JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    public function send()
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
        echo $this->content;
    }
}
