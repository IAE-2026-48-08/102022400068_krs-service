<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-IAE-KEY');
        $expectedKey = config('services.iae.api_key');

        if (!$key || $key !== $expectedKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access. X-IAE-KEY header is missing or invalid.',
                'errors' => [
                    'auth' => ['Invalid API Key.']
                ]
            ], 401);
        }

        return $next($request);
    }
}
