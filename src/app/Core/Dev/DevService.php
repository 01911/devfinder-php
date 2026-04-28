<?php

namespace App\Core\Dev;

/**
 * Dev Service - Business logic for Dev operations
 * 
 * DDD: Application/Service Layer
 */
class DevService
{
    public function __construct(
        private DevRepository $repository
    ) {}

    /**
     * Create a new dev
     */
    public function create(string $username, string $name, string $avatar, ?string $bio = null): DevEntity
    {
        $existing = $this->repository->findByUsername($username);
        if ($existing) {
            throw new \DomainException("Dev with username '{$username}' already exists");
        }

        $dev = new DevEntity($name, $username, $avatar, $bio);
        return $this->repository->create($dev);
    }

    /**
     * Get dev by ID
     */
    public function getById(string $id): DevEntity
    {
        $dev = $this->repository->findById($id);
        if (!$dev) {
            throw new \DomainException("Dev not found with ID: {$id}");
        }
        return $dev;
    }

    /**
     * Get dev by username
     */
    public function getByUsername(string $username): DevEntity
    {
        $dev = $this->repository->findByUsername($username);
        if (!$dev) {
            throw new \DomainException("Dev not found with username: {$username}");
        }
        return $dev;
    }

    /**
     * Get all devs (paginated)
     * 
     * @return array{items: DevEntity[], total: int, page: int, perPage: int}
     */
    public function getAllPaginated(int $page = 1, int $perPage = 30): array
    {
        return $this->repository->findAll($page, $perPage);
    }

    /**
     * Get available devs (excluding current dev's likes/dislikes)
     * 
     * @return array{items: DevEntity[], total: int, page: int, perPage: int}
     */
    public function getAvailablePaginated(string $currentDevId, int $page = 1, int $perPage = 30): array
    {
        $currentDev = $this->getById($currentDevId);
        
        // Exclude current dev and devs already liked/disliked
        $excludeIds = array_merge(
            [$currentDevId],
            $currentDev->getLikes(),
            $currentDev->getDislikes()
        );

        return $this->repository->findAvailable($excludeIds, $page, $perPage);
    }

    /**
     * Like a dev
     */
    public function like(string $currentDevId, string $targetDevUsername): DevEntity
    {
        $currentDev = $this->getById($currentDevId);
        $targetDev = $this->repository->findByUsername($targetDevUsername);

        if (!$targetDev) {
            throw new \DomainException("Dev not found with username: {$targetDevUsername}");
        }

        $currentDev->addLike($targetDev->getId());
        return $this->repository->update($currentDev);
    }

    /**
     * Remove like from a dev
     */
    public function unlike(string $currentDevId, string $targetDevUsername): DevEntity
    {
        $currentDev = $this->getById($currentDevId);
        $targetDev = $this->repository->findByUsername($targetDevUsername);

        if (!$targetDev) {
            throw new \DomainException("Dev not found with username: {$targetDevUsername}");
        }

        $currentDev->removeLike($targetDev->getId());
        return $this->repository->update($currentDev);
    }

    /**
     * Dislike a dev
     */
    public function dislike(string $currentDevId, string $targetDevUsername): DevEntity
    {
        $currentDev = $this->getById($currentDevId);
        $targetDev = $this->repository->findByUsername($targetDevUsername);

        if (!$targetDev) {
            throw new \DomainException("Dev not found with username: {$targetDevUsername}");
        }

        $currentDev->addDislike($targetDev->getId());
        return $this->repository->update($currentDev);
    }

    /**
     * Remove dislike from a dev
     */
    public function undislike(string $currentDevId, string $targetDevUsername): DevEntity
    {
        $currentDev = $this->getById($currentDevId);
        $targetDev = $this->repository->findByUsername($targetDevUsername);

        if (!$targetDev) {
            throw new \DomainException("Dev not found with username: {$targetDevUsername}");
        }

        $currentDev->removeDislike($targetDev->getId());
        return $this->repository->update($currentDev);
    }

    /**
     * Get likes for a dev
     * 
     * @return DevEntity[]
     */
    public function getLikes(string $devId): array
    {
        $dev = $this->getById($devId);
        $likes = [];

        foreach ($dev->getLikes() as $likedDevId) {
            $likedDev = $this->repository->findById($likedDevId);
            if ($likedDev) {
                $likes[] = $likedDev;
            }
        }

        return $likes;
    }
}
