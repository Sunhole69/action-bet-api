<?php

namespace App\Http\Middleware;

use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use Closure;
use Illuminate\Http\Request;

class TokenCheck
{
    use APIResponse;
    use AuthUserManager;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken()){
            return $this->errorResponse(array([
                'error' => 'Unauthenticated user',
                'message' => 'Please send your access token to access this resources'
            ]),401);
        }

        if (!$this->getCurrentUser($request)){
            return $this->errorResponse(array([
                'error' => 'Invalid token',
                'message' => 'Please login again to get another token'
            ]),404);
        }

        return $next($request);
    }
}
