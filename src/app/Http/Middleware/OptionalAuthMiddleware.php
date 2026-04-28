<?php

namespace App\Http\Middleware;

use App\Infrastructure\Authentication\JWTAuth;
use Closure;
use Illuminate\Http\Request;

/**
 * Optional JWT Authentication Middleware
 * 
 * Extracts JWT token if present, but doesn't require it
 */
class OptionalAuthMiddleware
{
    public function __construct(
        private JWTAuth $jwt
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $token = JWTAuth::extractTokenFromHeader(
            $request->header('Authorization')
        );

        if ($token) {
            $decoded = $this->jwt->verifyToken($token);
            
            if ($decoded) {
                $request->merge([
                    'auth' => [
                        'id' => $decoded['id'],
                        'username' => $decoded['username'],
                    ]
                ]);
            }
        }

        return $next($request);
    }
}
