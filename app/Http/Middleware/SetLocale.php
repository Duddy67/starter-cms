<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use URL;

class SetLocale
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
        app()->setLocale($request->segment(1));

        URL::defaults(['locale' => $request->segment(1)]);

        return $next($request);
    }
}
