<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = JWTAuth::parseToken();
            $user = $token->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 200);
            }
            $request->merge(['user' => $user]);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 200);
        }

        return $next($request);
    }
}
