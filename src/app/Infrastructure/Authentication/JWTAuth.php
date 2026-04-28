<?php

namespace App\Infrastructure\Authentication;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Core\Dev\DevEntity;

/**
 * JWT Authentication Service
 * 
 * DDD: Infrastructure Layer - Authentication
 */
class JWTAuth
{
    private string $secret;
    private string $algorithm;
    private int $expiresIn;

    public function __construct()
    {
        $this->secret = config('auth.secret');
        $this->algorithm = config('auth.algorithm');
        $this->expiresIn = config('auth.expires_in');
    }

    /**
     * Generate JWT token for a dev
     */
    public function generateToken(DevEntity $dev): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->expiresIn;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'id' => $dev->getId(),
            'username' => $dev->getUsername(),
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Verify and decode JWT token
     * 
     * @return array{id: string, username: string, iat: int, exp: int}|null
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract token from Bearer header
     */
    public static function extractTokenFromHeader(?string $authHeader): ?string
    {
        if (!$authHeader) {
            return null;
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }
}
