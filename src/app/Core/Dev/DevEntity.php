<?php

namespace App\Core\Dev;

use DateTimeImmutable;

/**
 * Dev Entity - Represents a developer in the system
 * 
 * DDD: Value Object / Entity
 */
class DevEntity
{
    private ?string $id = null;
    private string $name;
    private string $username;
    private string $avatar;
    private ?string $bio = null;
    /** @var string[] */
    private array $likes = [];
    /** @var string[] */
    private array $dislikes = [];
    /** @var string[] */
    private array $followedChannels = [];
    /** @var string[] */
    private array $ignoredChannels = [];
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        string $username,
        string $avatar,
        ?string $bio = null
    ) {
        $this->name = $name;
        $this->username = $username;
        $this->avatar = $avatar;
        $this->bio = $bio;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getLikes(): array
    {
        return $this->likes;
    }

    public function addLike(string $devId): void
    {
        if (!in_array($devId, $this->likes, true)) {
            $this->likes[] = $devId;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeLike(string $devId): void
    {
        $this->likes = array_filter($this->likes, fn($id) => $id !== $devId);
        if (count($this->likes) !== count($this->likes)) {
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getDislikes(): array
    {
        return $this->dislikes;
    }

    public function addDislike(string $devId): void
    {
        if (!in_array($devId, $this->dislikes, true)) {
            $this->dislikes[] = $devId;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeDislike(string $devId): void
    {
        $this->dislikes = array_filter($this->dislikes, fn($id) => $id !== $devId);
        if (count($this->dislikes) !== count($this->dislikes)) {
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getFollowedChannels(): array
    {
        return $this->followedChannels;
    }

    public function followChannel(string $channelId): void
    {
        if (!in_array($channelId, $this->followedChannels, true)) {
            $this->followedChannels[] = $channelId;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function unfollowChannel(string $channelId): void
    {
        $this->followedChannels = array_filter($this->followedChannels, fn($id) => $id !== $channelId);
        if (count($this->followedChannels) !== count($this->followedChannels)) {
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getIgnoredChannels(): array
    {
        return $this->ignoredChannels;
    }

    public function ignoreChannel(string $channelId): void
    {
        if (!in_array($channelId, $this->ignoredChannels, true)) {
            $this->ignoredChannels[] = $channelId;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function unignoreChannel(string $channelId): void
    {
        $this->ignoredChannels = array_filter($this->ignoredChannels, fn($id) => $id !== $channelId);
        if (count($this->ignoredChannels) !== count($this->ignoredChannels)) {
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user' => $this->username,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'likes' => $this->likes,
            'deslikes' => $this->dislikes,
            'follow' => $this->followedChannels,
            'ignore' => $this->ignoredChannels,
            'createdAt' => $this->createdAt->toDateTimeString(),
            'updatedAt' => $this->updatedAt->toDateTimeString(),
        ];
    }
}
