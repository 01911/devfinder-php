<?php

namespace App\Http\Middleware;

use App\Infrastructure\Authentication\JWTAuth;
use Closure;
use Illuminate\Http\Request;

/**
 * JWT Authentication Middleware
 * 
 * Validates JWT token from Bearer header and sets authenticated user
 */
class AuthMiddleware
{
    public function __construct(
        private JWTAuth $jwt
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $token = JWTAuth::extractTokenFromHeader(
            $request->header('Authorization')
        );

        if (!$token) {
            return response()->json(['error' => 'Token not provided.'], 401);
        }

        $decoded = $this->jwt->verifyToken($token);

        if (!$decoded) {
            return response()->json(['error' => 'Token invalid.'], 401);
        }

        // Store authenticated user in request
        $request->merge([
            'auth' => [
                'id' => $decoded['id'],
                'username' => $decoded['username'],
            ]
        ]);

        return $next($request);
    }
}
