<?php

namespace Illuminate\Http;

/**
 * Request Object - Wrapper around superglobals
 */
class Request
{
    public array $query = [];
    public array $post = [];
    public array $server = [];
    public array $headers = [];
    public array $auth = [];

    public function input(string $key, $default = null)
    {
        // Try POST first, then GET
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }
        if (isset($this->query[$key])) {
            return $this->query[$key];
        }

        // Try JSON body
        $json = json_decode(file_get_contents('php://input'), true);
        if (is_array($json) && isset($json[$key])) {
            return $json[$key];
        }

        return $default;
    }

    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function header(string $key): ?string
    {
        // Case-insensitive header lookup
        foreach ($this->headers as $k => $v) {
            if (strtolower($k) === strtolower($key)) {
                return $v;
            }
        }
        return null;
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->query[$key]) || isset($this->auth[$key]);
    }

    public function get(string $key, $default = null)
    {
        if (isset($this->auth[$key])) {
            return $this->auth[$key];
        }
        return $this->input($key, $default);
    }

    public function merge(array $data): void
    {
        foreach ($data as $key => $value) {
            if ($key === 'auth') {
                $this->auth = $value;
            } else {
                $this->post[$key] = $value;
            }
        }
    }

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function json(): ?array
    {
        return json_decode(file_get_contents('php://input'), true);
    }
}

/**
 * JSON Response Object
 */
class JsonResponse
{
    public function __construct(
        private array $data,
        private int $status = 200,
        private array $headers = []
    ) {
        $this->headers['Content-Type'] = 'application/json';
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
        echo json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function create(array $data, int $status = 200): self
    {
        return new self($data, $status);
    }
}

/**
 * Response helper
 */
function response()
{
    return new class {
        public function json($data, $status = 200)
        {
            return JsonResponse::create($data, $status);
        }
    };
}
