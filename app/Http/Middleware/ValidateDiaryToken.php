<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateDiaryToken
{
    /**
     * Handle an incoming request.
     *
     * Validates that the request includes a valid diary_token in the X-DIARY-TOKEN header.
     * This provides a second layer of authentication beyond the session cookie.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $diaryToken = $request->header('X-DIARY-TOKEN');

        if (empty($diaryToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Diary token required.',
                'code' => 'DIARY_TOKEN_MISSING',
            ], 401);
        }

        if (!$user->verifyDiaryToken($diaryToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid diary token.',
                'code' => 'DIARY_TOKEN_INVALID',
            ], 401);
        }

        return $next($request);
    }
}
