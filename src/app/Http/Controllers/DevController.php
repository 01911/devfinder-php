<?php

namespace App\Http\Controllers;

use App\Core\Dev\DevService;
use App\Infrastructure\Authentication\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dev Controller
 * 
 * HTTP Layer: Handles dev-related requests
 */
class DevController
{
    public function __construct(
        private DevService $service,
        private JWTAuth $jwt
    ) {}

    /**
     * GET /v1/devs - Get all devs with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $page = max(1, $page);

            if ($request->has('auth')) {
                // Logged in: get available devs (excluding likes/dislikes)
                $result = $this->service->getAvailablePaginated(
                    $request->get('auth')['id'],
                    $page
                );
            } else {
                // Not logged in: get all devs
                $result = $this->service->getAllPaginated($page);
            }

            return response()->json([
                'docs' => array_map(fn($dev) => $dev->toArray(), $result['items']),
                'total' => $result['total'],
                'itemsPerPage' => $result['perPage'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * GET /v1/devs/:username - Get dev by username
     */
    public function show(Request $request, string $username): JsonResponse
    {
        try {
            $dev = $this->service->getByUsername($username);
            return response()->json($dev->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/devs - Create new dev
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate auth (from AuthMiddleware)
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            // Validate input
            $username = $request->input('username');
            if (!$username || !is_string($username)) {
                return response()->json(['error' => 'Invalid username'], Response::HTTP_BAD_REQUEST);
            }

            // In a real app, you would fetch from GitHub API here
            // For now, using mock data
            $dev = $this->service->create(
                username: $username,
                name: $request->input('name', $username),
                avatar: $request->input('avatar', 'https://github.com/api/user/avatar'),
                bio: $request->input('bio')
            );

            // Generate token
            $token = $this->jwt->generateToken($dev);

            return response()->json([
                'dev' => $dev->toArray(),
                'token' => $token,
            ], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * GET /v1/me - Get current authenticated dev
     */
    public function me(Request $request): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $dev = $this->service->getById($request->get('auth')['id']);
            return response()->json($dev->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/devs/:username/like - Like a dev
     */
    public function like(Request $request, string $username): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $dev = $this->service->like(
                $request->get('auth')['id'],
                $username
            );

            return response()->json($dev->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * DELETE /v1/devs/:username/like - Unlike a dev
     */
    public function unlike(Request $request, string $username): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $dev = $this->service->unlike(
                $request->get('auth')['id'],
                $username
            );

            return response()->json($dev->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/devs/:username/dislike - Dislike a dev
     */
    public function dislike(Request $request, string $username): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $dev = $this->service->dislike(
                $request->get('auth')['id'],
                $username
            );

            return response()->json($dev->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * DELETE /v1/devs/:username/dislike - Remove dislike from dev
     */
    public function undislike(Request $request, string $username): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $dev = $this->service->undislike(
                $request->get('auth')['id'],
                $username
            );

            return response()->json($dev->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * GET /v1/devs/:username/likes - Get likes for a dev
     */
    public function likes(Request $request, string $username): JsonResponse
    {
        try {
            $dev = $this->service->getByUsername($username);
            $likes = $this->service->getLikes($dev->getId());

            return response()->json(array_map(fn($dev) => $dev->toArray(), $likes));
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
