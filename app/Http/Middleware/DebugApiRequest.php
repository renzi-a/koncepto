<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        if (str_starts_with($request->path(), 'api/admin/orders')) {
            Log::info('--- DebugApiRequest Middleware (from app/Http/Middleware) ---');
            Log::info('URL:', ['url' => $request->fullUrl()]);
            Log::info('Method:', ['method' => $request->method()]);
            Log::info('Headers:', $request->headers->all());
            Log::info('Authorization Header Raw:', ['auth' => $request->header('Authorization')]);
            Log::info('Request IP:', ['ip' => $request->ip()]);
            Log::info('--- End DebugApiRequest Middleware ---');
        }

        return $next($request);
    }
}