<?php

namespace Illuminate\Foundation;

class Application
{
    protected $bindings = [];
    protected $singletons = [];

    public function __construct(
        protected string $basePath
    ) {}

    public static function configure(string $basePath): static
    {
        return new static($basePath);
    }

    public function withRouting(array $routes): static
    {
        // Store routes for later loading
        return $this;
    }

    public function withMiddleware(\Closure $callback): static
    {
        return $this;
    }

    public function withExceptions(\Closure $callback): static
    {
        return $this;
    }

    public function create(): static
    {
        return $this;
    }

    public function make(string $abstract, array $parameters = [])
    {
        // Singleton resolution
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }

        // Binding resolution
        if (isset($this->bindings[$abstract])) {
            $binding = $this->bindings[$abstract];
            return is_callable($binding) ? $binding($this, ...$parameters) : $binding;
        }

        // Auto-resolve via reflection
        try {
            $reflection = new \ReflectionClass($abstract);
            if ($reflection->isInstantiable()) {
                return new $abstract(...$parameters);
            }
        } catch (\ReflectionException) {
            //
        }

        throw new \InvalidArgumentException("Can't resolve {$abstract}");
    }

    public function bind(string $abstract, $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->singletons[$abstract] = $this->make($abstract);
    }

    public function handle($request)
    {
        // Route the request
        return $this->routeRequest($request);
    }

    private function routeRequest($request)
    {
        // Very simple routing
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Remove base path prefix if exists
        if (str_starts_with($path, '/v1')) {
            // Route to API endpoints
            return $this->handleApiRoute($path, $method, $request);
        }

        return new \stdClass(); // Placeholder
    }

    private function handleApiRoute($path, $method, $request)
    {
        // Placeholder for request handling
        return new \stdClass();
    }
}
