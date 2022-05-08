<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPostSettings
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
	$routeName = $request->route()->getName();

	if ($routeName == 'admin.post.settings.index' && !auth()->user()->isAllowedTo('post-settings')) {
	    return redirect()->route('admin')->with('error', __('messages.generic.access_not_auth'));
	}

        return $next($request);
    }
}
