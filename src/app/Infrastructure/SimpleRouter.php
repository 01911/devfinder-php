<?php

namespace App\Infrastructure;

use Illuminate\Http\Request;
use App\Http\Middleware\OptionalAuthMiddleware;
use App\Http\Middleware\AuthMiddleware;

/**
 * Simple Router - Routes HTTP requests to controllers
 * 
 * This is a lightweight router for the API without full framework overhead
 */
class SimpleRouter
{
    private array $routes = [];

    public function __construct()
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // App Info
        $this->get('/', fn() => response()->json(['appname' => 'DevFinder']));

        // Dev Routes
        $this->get('/devs', [DevController::class, 'index']);
        $this->post('/devs', [DevController::class, 'store'], auth: true);
        $this->get('/devs/{username}', [DevController::class, 'show']);
        $this->get('/me', [DevController::class, 'me'], auth: true);
        $this->post('/devs/{username}/like', [DevController::class, 'like'], auth: true);
        $this->delete('/devs/{username}/like', [DevController::class, 'unlike'], auth: true);
        $this->post('/devs/{username}/dislike', [DevController::class, 'dislike'], auth: true);
        $this->delete('/devs/{username}/dislike', [DevController::class, 'undislike'], auth: true);
        $this->get('/devs/{username}/likes', [DevController::class, 'likes']);

        // Channel Routes
        $this->get('/channels', [ChannelController::class, 'index']);
        $this->get('/channels/{nameOrLink}', [ChannelController::class, 'show']);
        $this->post('/channels', [ChannelController::class, 'store'], auth: true);
        $this->post('/channels/{channelId}/like', [ChannelController::class, 'like'], auth: true);
        $this->delete('/channels/{channelId}/like', [ChannelController::class, 'unlike'], auth: true);
        $this->post('/channels/{channelId}/follow', [ChannelController::class, 'follow'], auth: true);
        $this->post('/channels/refresh', [ChannelController::class, 'refresh'], auth: true);

        // Video Routes
        $this->get('/videos', [VideoController::class, 'index']);
        $this->get('/video/{videoId}', [VideoController::class, 'show']);
        $this->get('/trending', [VideoController::class, 'trending']);
        $this->get('/subscriptions', [VideoController::class, 'subscriptions'], auth: true);
        $this->post('/video', [VideoController::class, 'store'], auth: true);
        $this->post('/video/{videoId}/refresh', [VideoController::class, 'refresh'], auth: true);
    }

    public function get(string $path, $handler, bool $auth = false): void
    {
        $this->register('GET', $path, $handler, $auth);
    }

    public function post(string $path, $handler, bool $auth = false): void
    {
        $this->register('POST', $path, $handler, $auth);
    }

    public function delete(string $path, $handler, bool $auth = false): void
    {
        $this->register('DELETE', $path, $handler, $auth);
    }

    private function register(string $method, string $path, $handler, bool $auth = false): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'auth' => $auth,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Remove /v1 prefix
        if (str_starts_with($path, '/v1')) {
            $path = substr($path, 3);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchesPath($path, $route['path'])) {
                // Handle auth middleware
                if ($route['auth']) {
                    $authMiddleware = new AuthMiddleware(new \App\Infrastructure\Authentication\JWTAuth());
                    $response = $authMiddleware->handle($request, fn($req) => $this->callHandler($req, $route['handler'], $path, $route['path']));
                } else {
                    $optionalAuthMiddleware = new OptionalAuthMiddleware(new \App\Infrastructure\Authentication\JWTAuth());
                    $response = $optionalAuthMiddleware->handle($request, fn($req) => $this->callHandler($req, $route['handler'], $path, $route['path']));
                }

                if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
                    $response->send();
                    return;
                }

                echo json_encode($response);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    private function matchesPath(string $requestPath, string $routePath): bool
    {
        $requestParts = array_filter(explode('/', $requestPath));
        $routeParts = array_filter(explode('/', $routePath));

        if (count($requestParts) !== count($routeParts)) {
            return false;
        }

        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                // Dynamic parameter - always matches
                continue;
            }

            if ($requestParts[$i] !== $part) {
                return false;
            }
        }

        return true;
    }

    private function callHandler(Request $request, $handler, string $requestPath, string $routePath): mixed
    {
        // Extract path parameters
        $params = $this->extractPathParams($requestPath, $routePath);

        if (is_array($handler)) {
            $controller = new $handler[0](...$this->resolveDependencies($handler[0]));
            return $controller->{$handler[1]}($request, ...$params);
        }

        return $handler();
    }

    private function extractPathParams(string $requestPath, string $routePath): array
    {
        $requestParts = array_filter(explode('/', $requestPath));
        $routeParts = array_filter(explode('/', $routePath));

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[] = $requestParts[$i] ?? null;
            }
        }

        return $params;
    }

    private function resolveDependencies(string $class): array
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return [];
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType) {
                $dependencies[] = $this->resolveClass($type->getName());
            }
        }

        return $dependencies;
    }

    private function resolveClass(string $className)
    {
        // Resolve common service classes
        return match ($className) {
            \App\Core\Dev\DevService::class => new \App\Core\Dev\DevService(
                new \App\Infrastructure\Repositories\InMemoryDevRepository()
            ),
            \App\Core\Channel\ChannelService::class => new \App\Core\Channel\ChannelService(
                new \App\Infrastructure\Repositories\InMemoryChannelRepository()
            ),
            \App\Core\Video\VideoService::class => new \App\Core\Video\VideoService(
                new \App\Infrastructure\Repositories\InMemoryVideoRepository()
            ),
            \App\Infrastructure\Authentication\JWTAuth::class => new \App\Infrastructure\Authentication\JWTAuth(),
            default => null,
        };
    }
}
