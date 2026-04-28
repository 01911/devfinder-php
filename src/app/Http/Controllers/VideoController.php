<?php

namespace App\Http\Controllers;

use App\Core\Video\VideoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Video Controller
 * 
 * HTTP Layer: Handles video-related requests
 */
class VideoController
{
    public function __construct(
        private VideoService $service
    ) {}

    /**
     * GET /v1/videos - Get all videos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $page = max(1, $page);

            $result = $this->service->getAllPaginated($page);

            return response()->json([
                'docs' => array_map(fn($video) => $video->toArray(), $result['items']),
                'total' => $result['total'],
                'itemsPerPage' => $result['perPage'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * GET /v1/video/:videoId - Get video by ID
     */
    public function show(Request $request, string $videoId): JsonResponse
    {
        try {
            $video = $this->service->getById($videoId);
            return response()->json($video->toArray());
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/video - Create new video
     */
    public function store(Request $request): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            $title = $request->input('title');
            $url = $request->input('url');
            $channel = $request->input('channel');
            $channelUrl = $request->input('channel_url');
            $thumbnail = $request->input('thumbnail');

            if (!$title || !$url || !$channel || !$channelUrl || !$thumbnail) {
                return response()->json(
                    ['error' => 'Missing required fields'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $video = $this->service->create(
                title: $title,
                url: $url,
                channel: $channel,
                channelUrl: $channelUrl,
                thumbnail: $thumbnail,
                channelId: $request->input('channel_id'),
                channelIcon: $request->input('channel_icon'),
                viewCount: $request->input('viewnum')
            );

            return response()->json($video->toArray(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * GET /v1/trending - Get trending videos (most viewed)
     */
    public function trending(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $page = max(1, $page);

            $result = $this->service->getTrendingPaginated($page);

            return response()->json([
                'docs' => array_map(fn($video) => $video->toArray(), $result['items']),
                'total' => $result['total'],
                'itemsPerPage' => $result['perPage'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * GET /v1/subscriptions - Get videos from followed channels
     */
    public function subscriptions(Request $request): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            // This would need integration with dev service to get followed channels
            $page = (int) $request->query('page', 1);

            $result = $this->service->getAllPaginated($page);

            return response()->json([
                'docs' => array_map(fn($video) => $video->toArray(), $result['items']),
                'total' => $result['total'],
                'itemsPerPage' => $result['perPage'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /v1/videos/:videoId/refresh - Refresh video data from YouTube
     */
    public function refresh(Request $request, string $videoId): JsonResponse
    {
        try {
            if (!$request->has('auth')) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }

            // In a real implementation, this would fetch updated data from YouTube API
            return response()->json(['message' => 'Video refreshed'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
