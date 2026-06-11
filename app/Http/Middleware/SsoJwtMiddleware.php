<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use App\Models\Student;

class SsoJwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token tidak ditemukan'], 401);
        }

        try {
            // 1. Ambil JWKS (Public Key) dari Cloud Dosen
            $jwksUrl = config('services.iae.sso_url') . '/api/v1/auth/jwks';
            $jwksResponse = Http::get($jwksUrl);
            $jwks = $jwksResponse->json();

            // 2. Decode Token
            $decoded = JWT::decode($token, JWK::parseKeySet($jwks));

            // 3. Mapping user (Opsional: cocokkan $decoded->email dengan database lokal)
            // Misalnya, mengizinkan akses dan menyimpan data user ke request
            $request->attributes->add(['sso_user' => $decoded]);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token tidak valid: ' . $e->getMessage()], 401);
        }
    }
}