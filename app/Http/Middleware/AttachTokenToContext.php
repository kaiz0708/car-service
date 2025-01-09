<?php

namespace App\Http\Middleware;

use App\CustomAuthToken\TokenContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AttachTokenToContext
{
    public function handle(Request $request, Closure $next) : Response
    {

        $token = $request->bearerToken();
        Log::info('token : ' . $token);

        if ($token) {
            TokenContext::setToken($token);
        }

        return $next($request);
    }
}

