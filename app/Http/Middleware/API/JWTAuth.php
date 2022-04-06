<?php

namespace App\Http\Middleware\API;

use App\Http\Controllers\ResponsesController;
use Closure;
use Illuminate\Http\Request;

class JWTAuth extends ResponsesController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check())
            return $this->sendError("Unauthorised access blocked!");
        return $next($request);
    }
}
