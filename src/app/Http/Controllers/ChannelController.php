<?php

namespace App\Http\Controllers;

use App\Core\Channel\ChannelService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Channel Controller
 * 
 * HTTP Layer: Handles channel-related requests
 */
class ChannelController
{
    public function __construct(
        private ChannelService $service
    ) {}

    /**
     * GET /v1/channels - Get all channels
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $page = max(1, $page);

            $result = $this->service->getAllPaginated($page);

            return response()->json([
                'docs' => array_map(fn($channel) => $channel->toArray(), $result['items']),
                'total' => $result['total'],
                'itemsPerPage' => $result['perPage'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * GET /v1/channels/:nameOrLink - Get channel by name or link
     */
    public function show(Request $request, string $nameOrLink): JsonResponse
    {
        try {
            try {
                $channel = $this->service->getByName($nameOrLink);
            } catch (\DomainException) {
                // Try by link if name not found
                if (filter_var($nameOrLink, FILTER_VALIDATE_URL)) {
                    $channel = $this->service->getById($nameOrLink);
                } else {
                    throw new \DomainException('Channel not found');
                }
            }

            return response()->json($channel->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/channels - Create new channel
     */
    public function store(Request $request): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $name = $request->input('title');
            $link = $request->input('link');
            $category = $request->input('category');
            $description = $request->input('description');
            $tags = $request->input('tags', []);

            if (!$name || !$link || !$category) {
                return response()->json(
                    ['error' => 'Missing required fields: title, link, category'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $channel = $this->service->create(
                name: $name,
                link: $link,
                category: $category,
                description: $description,
                tags: $tags
            );

            return response()->json($channel->toArray(), Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/channels/:channelId/like - Like a channel
     */
    public function like(Request $request, string $channelId): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $channel = $this->service->like($request->get('auth')['id'], $channelId);
            return response()->json($channel->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * DELETE /v1/channels/:channelId/like - Unlike a channel
     */
    public function unlike(Request $request, string $channelId): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $channel = $this->service->unlike($request->get('auth')['id'], $channelId);
            return response()->json($channel->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/channels/:channelId/follow - Follow a channel
     */
    public function follow(Request $request, string $channelId): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            // This would need dev service integration
            // For now, just acknowledge the action
            return response()->json(['message' => 'Channel followed'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/channels/refresh - Refresh channel videos from YouTube
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            // In a real implementation, this would fetch from YouTube API
            return response()->json(['message' => 'Channels refreshed'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
