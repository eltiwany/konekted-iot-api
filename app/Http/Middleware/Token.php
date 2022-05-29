<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ResponsesController;
use App\Models\UserBoard;
use Closure;
use Illuminate\Http\Request;

class Token extends ResponsesController
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
        if (!$request->get('token'))
            return $this->sendError('Token is not provided!', [], 401);

        $userActiveBoard = UserBoard::where('token', $request->get('token'))->first();
        if (!$userActiveBoard)
            return $this->sendError('No board found or invalid token provided!', [], 404);

        return $next($request);
    }
}
