<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error' => 'Invalid token'], 401);
            } else if ($e instanceof TokenExpiredException) {
                return response()->json(['error' => 'Token has expired'], 401);
            } else {
                return response()->json(['error' => 'Authorization token not found'], 401);
            }
        }
        
        return $next($request);
    }
}