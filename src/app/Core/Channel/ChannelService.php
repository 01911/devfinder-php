<?php

namespace App\Core\Channel;

/**
 * Channel Service - Business logic for Channel operations
 * 
 * DDD: Application/Service Layer
 */
class ChannelService
{
    public function __construct(
        private ChannelRepository $repository
    ) {}

    /**
     * Create a new channel
     */
    public function create(
        string $name,
        string $link,
        string $category,
        ?string $description = null,
        ?string $avatar = null,
        ?string $userGithub = null,
        array $tags = []
    ): ChannelEntity {
        $existing = $this->repository->findByName($name);
        if ($existing) {
            throw new \DomainException("Channel with name '{$name}' already exists");
        }

        $channel = new ChannelEntity($name, $link, $category, $description, $avatar, $userGithub, $tags);
        return $this->repository->create($channel);
    }

    /**
     * Get channel by ID
     */
    public function getById(string $id): ChannelEntity
    {
        $channel = $this->repository->findById($id);
        if (!$channel) {
            throw new \DomainException("Channel not found with ID: {$id}");
        }
        return $channel;
    }

    /**
     * Get channel by name
     */
    public function getByName(string $name): ChannelEntity
    {
        $channel = $this->repository->findByName($name);
        if (!$channel) {
            throw new \DomainException("Channel not found with name: {$name}");
        }
        return $channel;
    }

    /**
     * Get all channels (paginated)
     * 
     * @return array{items: ChannelEntity[], total: int, page: int, perPage: int}
     */
    public function getAllPaginated(int $page = 1, int $perPage = 30): array
    {
        return $this->repository->findAll($page, $perPage);
    }

    /**
     * Like a channel
     */
    public function like(string $devId, string $channelId): ChannelEntity
    {
        $channel = $this->getById($channelId);
        $channel->addLike($devId);
        return $this->repository->update($channel);
    }

    /**
     * Remove like from a channel
     */
    public function unlike(string $devId, string $channelId): ChannelEntity
    {
        $channel = $this->getById($channelId);
        $channel->removeLike($devId);
        return $this->repository->update($channel);
    }

    /**
     * Dislike a channel
     */
    public function dislike(string $devId, string $channelId): ChannelEntity
    {
        $channel = $this->getById($channelId);
        $channel->addDislike($devId);
        return $this->repository->update($channel);
    }

    /**
     * Remove dislike from a channel
     */
    public function undislike(string $devId, string $channelId): ChannelEntity
    {
        $channel = $this->getById($channelId);
        $channel->removeDislike($devId);
        return $this->repository->update($channel);
    }

    /**
     * Update channel avatar (e.g., after fetching from YouTube)
     */
    public function updateAvatar(string $channelId, string $avatarUrl): ChannelEntity
    {
        $channel = $this->getById($channelId);
        $channel->setAvatar($avatarUrl);
        return $this->repository->update($channel);
    }
}
