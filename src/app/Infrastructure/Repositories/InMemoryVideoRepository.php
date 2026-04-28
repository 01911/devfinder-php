<?php

namespace App\Infrastructure\Repositories;

use App\Core\Video\VideoEntity;
use App\Core\Video\VideoRepository;

/**
 * In-Memory Video Repository Implementation
 * 
 * DDD: Infrastructure Layer - Repository Implementation
 */
class InMemoryVideoRepository implements VideoRepository
{
    /** @var array<string, VideoEntity> */
    private static array $videos = [];

    private static int $nextId = 1;

    public function create(VideoEntity $video): VideoEntity
    {
        $id = (string) self::$nextId++;
        $video->setId($id);
        self::$videos[$id] = $video;
        return $video;
    }

    public function findById(string $id): ?VideoEntity
    {
        return self::$videos[$id] ?? null;
    }

    public function findAll(int $page = 1, int $perPage = 30): array
    {
        return $this->paginate(self::$videos, $page, $perPage);
    }

    public function findByChannelId(string $channelId, int $page = 1, int $perPage = 30): array
    {
        $filtered = array_filter(
            self::$videos,
            fn(VideoEntity $video) => $video->getChannelId() === $channelId
        );

        return $this->paginate($filtered, $page, $perPage);
    }

    public function findTrending(int $page = 1, int $perPage = 30): array
    {
        $items = array_values(self::$videos);

        // Sort by view count descending
        usort($items, fn($a, $b) => ($b->getViewCount() ?? 0) <=> ($a->getViewCount() ?? 0));

        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($items, $offset, $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
        ];
    }

    public function update(VideoEntity $video): VideoEntity
    {
        if ($video->getId() === null) {
            throw new \InvalidArgumentException('Video must have an ID to update');
        }

        self::$videos[$video->getId()] = $video;
        return $video;
    }

    public function delete(string $id): bool
    {
        if (isset(self::$videos[$id])) {
            unset(self::$videos[$id]);
            return true;
        }
        return false;
    }

    public function count(): int
    {
        return count(self::$videos);
    }

    /**
     * @param VideoEntity[] $items
     * @return array{items: VideoEntity[], total: int, page: int, perPage: int}
     */
    private function paginate(array $items, int $page, int $perPage): array
    {
        $total = count($items);
        $items = array_values($items);

        usort($items, fn($a, $b) => $b->getPublishedAt() <=> $a->getPublishedAt());

        $offset = ($page - 1) * $perPage;
        $items = array_slice($items, $offset, $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
        ];
    }

    public static function clear(): void
    {
        self::$videos = [];
        self::$nextId = 1;
    }
}
