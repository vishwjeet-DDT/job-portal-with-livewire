<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetExecutionTimeLimit
{
    public function handle(Request $request, Closure $next)
    {
        set_time_limit((int) config('app.execution_time_limit', 120));
        
        ini_set('memory_limit', '2G');

        return $next($request);
    }
}
