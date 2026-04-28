<?php

namespace App\Infrastructure\Repositories;

use App\Core\Channel\ChannelEntity;
use App\Core\Channel\ChannelRepository;

/**
 * In-Memory Channel Repository Implementation
 * 
 * DDD: Infrastructure Layer - Repository Implementation
 */
class InMemoryChannelRepository implements ChannelRepository
{
    /** @var array<string, ChannelEntity> */
    private static array $channels = [];

    private static int $nextId = 1;

    public function create(ChannelEntity $channel): ChannelEntity
    {
        $id = (string) self::$nextId++;
        $channel->setId($id);
        self::$channels[$id] = $channel;
        return $channel;
    }

    public function findById(string $id): ?ChannelEntity
    {
        return self::$channels[$id] ?? null;
    }

    public function findByName(string $name): ?ChannelEntity
    {
        foreach (self::$channels as $channel) {
            if (strtolower($channel->getName()) === strtolower($name)) {
                return $channel;
            }
        }
        return null;
    }

    public function findByLink(string $link): ?ChannelEntity
    {
        foreach (self::$channels as $channel) {
            if ($channel->getLink() === $link) {
                return $channel;
            }
        }
        return null;
    }

    public function findAll(int $page = 1, int $perPage = 30): array
    {
        return $this->paginate(self::$channels, $page, $perPage);
    }

    public function update(ChannelEntity $channel): ChannelEntity
    {
        if ($channel->getId() === null) {
            throw new \InvalidArgumentException('Channel must have an ID to update');
        }

        self::$channels[$channel->getId()] = $channel;
        return $channel;
    }

    public function delete(string $id): bool
    {
        if (isset(self::$channels[$id])) {
            unset(self::$channels[$id]);
            return true;
        }
        return false;
    }

    public function count(): int
    {
        return count(self::$channels);
    }

    /**
     * @param ChannelEntity[] $items
     * @return array{items: ChannelEntity[], total: int, page: int, perPage: int}
     */
    private function paginate(array $items, int $page, int $perPage): array
    {
        $total = count($items);
        $items = array_values($items);

        usort($items, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

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
        self::$channels = [];
        self::$nextId = 1;
    }
}
