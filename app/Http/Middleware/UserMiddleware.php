<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        if ($request->user()->role !== 'user') {
            return response()->json([
                'status' => false,
                'message' => 'Akses hanya untuk user'
            ], 403);
        }

        return $next($request);
    }
}