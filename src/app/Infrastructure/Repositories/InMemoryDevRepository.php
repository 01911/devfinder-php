<?php

namespace App\Infrastructure\Repositories;

use App\Core\Dev\DevEntity;
use App\Core\Dev\DevRepository;

/**
 * In-Memory Dev Repository Implementation
 * 
 * DDD: Infrastructure Layer - Repository Implementation
 * 
 * This implementation stores data in PHP static arrays (in-memory).
 * Data is lost when the application restarts (suitable for POC).
 */
class InMemoryDevRepository implements DevRepository
{
    /** @var array<string, DevEntity> */
    private static array $devs = [];

    private static int $nextId = 1;

    public function create(DevEntity $dev): DevEntity
    {
        $id = (string) self::$nextId++;
        $dev->setId($id);
        self::$devs[$id] = $dev;
        return $dev;
    }

    public function findById(string $id): ?DevEntity
    {
        return self::$devs[$id] ?? null;
    }

    public function findByUsername(string $username): ?DevEntity
    {
        foreach (self::$devs as $dev) {
            if ($dev->getUsername() === $username) {
                return $dev;
            }
        }
        return null;
    }

    public function findAll(int $page = 1, int $perPage = 30): array
    {
        return $this->paginate(self::$devs, $page, $perPage);
    }

    public function findAvailable(array $excludeIds = [], int $page = 1, int $perPage = 30): array
    {
        $available = array_filter(
            self::$devs,
            fn(DevEntity $dev) => !in_array($dev->getId(), $excludeIds, true)
        );

        return $this->paginate($available, $page, $perPage);
    }

    public function update(DevEntity $dev): DevEntity
    {
        if ($dev->getId() === null) {
            throw new \InvalidArgumentException('Dev must have an ID to update');
        }

        self::$devs[$dev->getId()] = $dev;
        return $dev;
    }

    public function delete(string $id): bool
    {
        if (isset(self::$devs[$id])) {
            unset(self::$devs[$id]);
            return true;
        }
        return false;
    }

    public function count(): int
    {
        return count(self::$devs);
    }

    /**
     * Paginate array of items
     * 
     * @param DevEntity[] $items
     * @return array{items: DevEntity[], total: int, page: int, perPage: int}
     */
    private function paginate(array $items, int $page, int $perPage): array
    {
        $total = count($items);
        $items = array_values($items); // Re-index array
        
        // Sort by createdAt descending
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

    /**
     * Clear all data (useful for testing)
     */
    public static function clear(): void
    {
        self::$devs = [];
        self::$nextId = 1;
    }
}
