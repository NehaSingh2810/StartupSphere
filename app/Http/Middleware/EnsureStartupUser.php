<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureStartupUser
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->has('startup_user')) {
            return redirect('/login')->with('status', 'Please login first.');
        }

        return $next($request);
    }
}
