<?php

namespace App\Core\Video;

/**
 * Video Repository Interface - Contract for Video persistence
 * 
 * DDD: Repository Interface
 */
interface VideoRepository
{
    /**
     * Create and persist a new video
     */
    public function create(VideoEntity $video): VideoEntity;

    /**
     * Find video by ID
     */
    public function findById(string $id): ?VideoEntity;

    /**
     * Get all videos
     */
    public function findAll(int $page = 1, int $perPage = 30): array;

    /**
     * Find videos by channel ID
     * 
     * @return array{items: VideoEntity[], total: int, page: int, perPage: int}
     */
    public function findByChannelId(string $channelId, int $page = 1, int $perPage = 30): array;

    /**
     * Find trending videos (most viewed)
     * 
     * @return array{items: VideoEntity[], total: int, page: int, perPage: int}
     */
    public function findTrending(int $page = 1, int $perPage = 30): array;

    /**
     * Update video
     */
    public function update(VideoEntity $video): VideoEntity;

    /**
     * Delete video by ID
     */
    public function delete(string $id): bool;

    /**
     * Count total videos
     */
    public function count(): int;
}
