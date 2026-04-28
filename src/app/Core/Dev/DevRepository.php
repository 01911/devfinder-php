<?php

namespace App\Core\Dev;

/**
 * Dev Repository Interface - Contract for Dev persistence
 * 
 * DDD: Repository Interface (Application/Domain boundary)
 */
interface DevRepository
{
    /**
     * Create and persist a new dev
     */
    public function create(DevEntity $dev): DevEntity;

    /**
     * Find dev by ID
     */
    public function findById(string $id): ?DevEntity;

    /**
     * Find dev by username
     */
    public function findByUsername(string $username): ?DevEntity;

    /**
     * Get all devs with pagination
     * 
     * @return array{items: DevEntity[], total: int, page: int, perPage: int}
     */
    public function findAll(int $page = 1, int $perPage = 30): array;

    /**
     * Get devs excluding ids with pagination
     * 
     * @param string[] $excludeIds
     * @return array{items: DevEntity[], total: int, page: int, perPage: int}
     */
    public function findAvailable(array $excludeIds = [], int $page = 1, int $perPage = 30): array;

    /**
     * Update dev
     */
    public function update(DevEntity $dev): DevEntity;

    /**
     * Delete dev by ID
     */
    public function delete(string $id): bool;

    /**
     * Count total devs
     */
    public function count(): int;
}
