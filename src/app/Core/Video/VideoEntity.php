<?php

namespace App\Core\Video;

use DateTimeImmutable;

/**
 * Video Entity - Represents a YouTube video in the system
 * 
 * DDD: Value Object / Entity
 */
class VideoEntity
{
    private ?string $id = null;
    private string $title;
    private string $url;
    private ?string $channelId = null;
    private string $channel;
    private string $channelUrl;
    private ?string $channelIcon = null;
    private string $thumbnail;
    private ?int $viewCount = null;
    private ?DateTimeImmutable $publishedAt = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $title,
        string $url,
        string $channel,
        string $channelUrl,
        string $thumbnail,
        ?string $channelId = null,
        ?string $channelIcon = null,
        ?int $viewCount = null,
        ?DateTimeImmutable $publishedAt = null
    ) {
        $this->title = $title;
        $this->url = $url;
        $this->channel = $channel;
        $this->channelUrl = $channelUrl;
        $this->thumbnail = $thumbnail;
        $this->channelId = $channelId;
        $this->channelIcon = $channelIcon;
        $this->viewCount = $viewCount;
        $this->publishedAt = $publishedAt ?? new DateTimeImmutable();
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getChannelUrl(): string
    {
        return $this->channelUrl;
    }

    public function getChannelIcon(): ?string
    {
        return $this->channelIcon;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    public function getViewCount(): ?int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $count): void
    {
        $this->viewCount = $count;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPublishedAt(): DateTimeImmutable
    {
        return $this->publishedAt;
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
            'title' => $this->title,
            'url' => $this->url,
            'channel_id' => $this->channelId,
            'channel' => $this->channel,
            'channel_url' => $this->channelUrl,
            'channel_icon' => $this->channelIcon,
            'thumbnail' => $this->thumbnail,
            'viewnum' => $this->viewCount,
            'date' => $this->publishedAt->toDateTimeString(),
            'createdAt' => $this->createdAt->toDateTimeString(),
            'updatedAt' => $this->updatedAt->toDateTimeString(),
        ];
    }
}
