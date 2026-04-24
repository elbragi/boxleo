<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRecruitmentApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY');
        $expectedKey = env('RECRUITMENT_API_KEY');

        if (!$apiKey || $apiKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing API Key'
            ], 401);
        }

        return $next($request);
    }
}
