<?php

namespace App\Core\Channel;

/**
 * Channel Repository Interface - Contract for Channel persistence
 * 
 * DDD: Repository Interface
 */
interface ChannelRepository
{
    /**
     * Create and persist a new channel
     */
    public function create(ChannelEntity $channel): ChannelEntity;

    /**
     * Find channel by ID
     */
    public function findById(string $id): ?ChannelEntity;

    /**
     * Find channel by name
     */
    public function findByName(string $name): ?ChannelEntity;

    /**
     * Find channel by link
     */
    public function findByLink(string $link): ?ChannelEntity;

    /**
     * Get all channels
     */
    public function findAll(int $page = 1, int $perPage = 30): array;

    /**
     * Update channel
     */
    public function update(ChannelEntity $channel): ChannelEntity;

    /**
     * Delete channel by ID
     */
    public function delete(string $id): bool;

    /**
     * Count total channels
     */
    public function count(): int;
}
