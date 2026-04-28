<?php

namespace App\Core\Channel;

use DateTimeImmutable;

/**
 * Channel Entity - Represents a YouTube channel in the system
 * 
 * DDD: Value Object / Entity
 */
class ChannelEntity
{
    private ?string $id = null;
    private string $name;
    private string $link;
    private ?string $avatar = null;
    private ?string $userGithub = null;
    private ?string $description = null;
    private string $category;
    /** @var string[] */
    private array $tags = [];
    /** @var string[] */
    private array $likes = [];
    /** @var string[] */
    private array $dislikes = [];
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        string $link,
        string $category,
        ?string $description = null,
        ?string $avatar = null,
        ?string $userGithub = null,
        array $tags = []
    ) {
        $this->name = $name;
        $this->link = $link;
        $this->category = $category;
        $this->description = $description;
        $this->avatar = $avatar;
        $this->userGithub = $userGithub;
        $this->tags = $tags;
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

    public function getLink(): string
    {
        return $this->link;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUserGithub(): ?string
    {
        return $this->userGithub;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getTags(): array
    {
        return $this->tags;
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
            'link' => $this->link,
            'avatar' => $this->avatar,
            'userGithub' => $this->userGithub,
            'description' => $this->description,
            'category' => $this->category,
            'tags' => $this->tags,
            'likes' => $this->likes,
            'deslikes' => $this->dislikes,
            'createdAt' => $this->createdAt->toDateTimeString(),
            'updatedAt' => $this->updatedAt->toDateTimeString(),
        ];
    }
}
