<?php

namespace App\Core\Video;

/**
 * Video Service - Business logic for Video operations
 * 
 * DDD: Application/Service Layer
 */
class VideoService
{
    public function __construct(
        private VideoRepository $repository
    ) {}

    /**
     * Create a new video
     */
    public function create(
        string $title,
        string $url,
        string $channel,
        string $channelUrl,
        string $thumbnail,
        ?string $channelId = null,
        ?string $channelIcon = null,
        ?int $viewCount = null
    ): VideoEntity {
        $video = new VideoEntity(
            $title,
            $url,
            $channel,
            $channelUrl,
            $thumbnail,
            $channelId,
            $channelIcon,
            $viewCount
        );

        return $this->repository->create($video);
    }

    /**
     * Get video by ID
     */
    public function getById(string $id): VideoEntity
    {
        $video = $this->repository->findById($id);
        if (!$video) {
            throw new \DomainException("Video not found with ID: {$id}");
        }
        return $video;
    }

    /**
     * Get all videos (paginated)
     * 
     * @return array{items: VideoEntity[], total: int, page: int, perPage: int}
     */
    public function getAllPaginated(int $page = 1, int $perPage = 30): array
    {
        return $this->repository->findAll($page, $perPage);
    }

    /**
     * Get videos by channel (paginated)
     * 
     * @return array{items: VideoEntity[], total: int, page: int, perPage: int}
     */
    public function getByChannelIdPaginated(string $channelId, int $page = 1, int $perPage = 30): array
    {
        return $this->repository->findByChannelId($channelId, $page, $perPage);
    }

    /**
     * Get trending videos (most viewed, paginated)
     * 
     * @return array{items: VideoEntity[], total: int, page: int, perPage: int}
     */
    public function getTrendingPaginated(int $page = 1, int $perPage = 30): array
    {
        return $this->repository->findTrending($page, $perPage);
    }

    /**
     * Update video view count
     */
    public function updateViewCount(string $videoId, int $viewCount): VideoEntity
    {
        $video = $this->getById($videoId);
        $video->setViewCount($viewCount);
        return $this->repository->update($video);
    }

    /**
     * Delete video
     */
    public function delete(string $videoId): bool
    {
        return $this->repository->delete($videoId);
    }
}
